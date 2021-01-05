<?php

namespace Biigle\Modules\Reports\Http\Requests;

use Biigle\Modules\Reports\ReportType;
use Biigle\Volume;
use Illuminate\Validation\Rule;

class StoreVolumeReport extends StoreReport
{
    /**
     * The volume to generate a new report for.
     *
     * @var Volume
     */
    public $volume;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->volume = Volume::findOrFail($this->route('id'));

        return $this->user()->can('access', $this->volume);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->volume->isImageVolume()) {
            $types = [
                ReportType::imageAnnotationsAreaId(),
                ReportType::imageAnnotationsBasicId(),
                ReportType::imageAnnotationsCsvId(),
                ReportType::imageAnnotationsExtendedId(),
                ReportType::imageAnnotationsFullId(),
                ReportType::imageAnnotationsAbundanceId(),
                ReportType::imageAnnotationsImageLocationId(),
                ReportType::imageLabelsBasicId(),
                ReportType::imageLabelsCsvId(),
                ReportType::imageLabelsImageLocationId(),
            ];
        } else {
            $types = [
                ReportType::videoAnnotationsCsvId(),
            ];
        }

        return array_merge(parent::rules(), [
            'type_id' => ['required', Rule::in($types)],
            'annotation_session_id' => "nullable|exists:annotation_sessions,id,volume_id,{$this->volume->id}",
        ]);
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        parent::withValidator($validator);

        $validator->after(function ($validator) {
            $typeId = intval($this->input('type_id'));
            $needsGeoInfo = [
                ReportType::imageAnnotationsImageLocationId(),
                ReportType::imageLabelsImageLocationId(),
            ];

            if (in_array($typeId, $needsGeoInfo) && !$this->volume->hasGeoInfo()) {
                $validator->errors()->add('id', 'The volume images have no geo coordinates.');
            }

            if ($typeId === ReportType::imageAnnotationsImageLocationId()) {
                $hasImagesWithMetadata = $this->volume->images()
                    ->whereNotNull('attrs->metadata->yaw')
                    ->whereNotNull('attrs->metadata->distance_to_ground')
                    ->exists();

                if (!$hasImagesWithMetadata) {
                    $validator->errors()->add('id', 'The volume images have no yaw and/or distance to ground metadata.');
                }

                $hasImagesWithDimensions = $this->volume->images()
                    ->whereNotNull('attrs->width')
                    ->whereNotNull('attrs->height')
                    ->exists();

                if (!$hasImagesWithDimensions) {
                    $validator->errors()->add('id', 'The volume images have no dimension information. Try again later if the images are new and still being processed.');
                }
            }
        });
    }

    /**
     * Get the options for the new report.
     *
     * @return array
     */
    public function getOptions()
    {
        return array_merge(parent::getOptions(), [
            'annotationSession' => $this->input('annotation_session_id'),
        ]);
    }
}
