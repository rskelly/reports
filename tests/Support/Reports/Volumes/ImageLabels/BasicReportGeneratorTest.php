<?php

namespace Biigle\Tests\Modules\Reports\Support\Reports\Volumes\ImageLabels;

use App;
use Mockery;
use TestCase;
use Biigle\Tests\LabelTest;
use Biigle\Tests\ImageTest;
use Biigle\Tests\VolumeTest;
use Biigle\Tests\ImageLabelTest;
use Biigle\Modules\Reports\Support\CsvFile;
use Biigle\Modules\Reports\Support\Reports\Volumes\ImageLabels\BasicReportGenerator;

class BasicReportGeneratorTest extends TestCase
{
    private $columns = ['image_id', 'image_filename', 'label_hierarchies'];

    public function testProperties()
    {
        $generator = new BasicReportGenerator;
        $this->assertEquals('basic image label report', $generator->getName());
        $this->assertEquals('basic_image_label_report', $generator->getFilename());
        $this->assertStringEndsWith('.xlsx', $generator->getFullFilename());
    }

    public function testGenerateReport()
    {
        $volume = VolumeTest::create();

        $il = ImageLabelTest::create([
            'image_id' => ImageTest::create([
                'volume_id' => $volume->id,
                'filename' => 'foo.jpg',
            ])->id,
        ]);

        $il2 = ImageLabelTest::create([
            'image_id' => $il->image_id,
        ]);

        $root = LabelTest::create();
        $child = LabelTest::create([
            'parent_id' => $root->id,
            'label_tree_id' => $root->label_tree_id,
        ]);

        $il3 = ImageLabelTest::create([
            'image_id' => ImageTest::create([
                'volume_id' => $volume->id,
                'filename' => 'bar.jpg',
            ])->id,
            'label_id' => $child->id,
        ]);

        $mock = Mockery::mock();

        $mock->shouldReceive('put')
            ->once()
            ->with([$volume->name]);

        $mock->shouldReceive('put')
            ->once()
            ->with($this->columns);

        $mock->shouldReceive('put')
            ->once()
            ->with([$il->image->id, $il->image->filename, "{$il->label->name}, {$il2->label->name}"]);

        $mock->shouldReceive('put')
            ->once()
            ->with([$il3->image->id, $il3->image->filename, "{$root->name} > {$child->name}"]);

        $mock->shouldReceive('close')
            ->once();

        App::singleton(CsvFile::class, function () use ($mock) {
            return $mock;
        });

        $generator = new BasicReportGenerator;
        $generator->setSource($volume);
        $mock = Mockery::mock();
        $mock->shouldReceive('run')->once();
        $generator->setPythonScriptRunner($mock);
        $generator->generateReport('my/path');
    }

    public function testGenerateReportSeparateLabelTrees()
    {
        $label1 = LabelTest::create();
        $label2 = LabelTest::create();

        $image = ImageTest::create();

        ImageLabelTest::create([
            'image_id' => $image->id,
            'label_id' => $label1->id,
        ]);
        ImageLabelTest::create([
            'image_id' => $image->id,
            'label_id' => $label2->id,
        ]);

        $mock = Mockery::mock();
        $mock->shouldReceive('put')
            ->once()
            ->with([$label1->tree->name]);

        $mock->shouldReceive('put')
            ->once()
            ->with([$label2->tree->name]);

        $mock->shouldReceive('put')
            ->twice()
            ->with($this->columns);

        $mock->shouldReceive('put')
            ->once()
            ->with([$image->id, $image->filename, $label1->name]);

        $mock->shouldReceive('put')
            ->once()
            ->with([$image->id, $image->filename, $label2->name]);

        $mock->shouldReceive('close')
            ->twice();

        App::singleton(CsvFile::class, function () use ($mock) {
            return $mock;
        });

        $generator = new BasicReportGenerator([
            'separateLabelTrees' => true,
        ]);
        $generator->setSource($image->volume);
        $mock = Mockery::mock();
        $mock->shouldReceive('run')->once();
        $generator->setPythonScriptRunner($mock);
        $generator->generateReport('my/path');
    }
}
