<?php

namespace Dias\Modules\Export\Http\Controllers\Api\Projects\ImageLabels;

use Dias\Modules\Export\Support\Reports\Projects\ImageLabels\CsvReport;
use Dias\Modules\Export\Http\Controllers\Api\Projects\ProjectReportController;

class CsvReportController extends ProjectReportController
{
    /**
     * The report classname
     *
     * @var string
     */
    protected $report = CsvReport::class;

    /**
     * @api {post} projects/:id/reports/image-labels/csv Generate a new csv image label report
     * @apiGroup Projects
     * @apiName GenerateCsvTransectImageLabelReport
     * @apiParam (Optional arguments) {Boolean} exportArea If `1`, restrict the report to the export area of the transect.
     * @apiPermission projectMember
     *
     * @apiParam {Number} id The transect ID.
     */
}
