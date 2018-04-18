<?php

namespace Biigle\Tests\Modules\Reports\Support\Reports\Volumes\ImageLabels;

use App;
use Mockery;
use TestCase;
use ZipArchive;
use Biigle\Tests\ImageTest;
use Biigle\Tests\LabelTest;
use Biigle\Tests\VolumeTest;
use Biigle\Tests\LabelTreeTest;
use Biigle\Tests\ImageLabelTest;
use Biigle\Modules\Reports\Support\CsvFile;
use Biigle\Modules\Reports\Support\Reports\Volumes\ImageLabels\CsvReportGenerator;

class CsvReportGeneratorTest extends TestCase
{
    private $columns = [
        'image_label_id',
        'image_id',
        'filename',
        'longitude',
        'latitude',
        'user_id',
        'firstname',
        'lastname',
        'label_id',
        'label_name',
        'label_hierarchy',
    ];

    public function testProperties()
    {
        $generator = new CsvReportGenerator;
        $this->assertEquals('CSV image label report', $generator->getName());
        $this->assertEquals('csv_image_label_report', $generator->getFilename());
        $this->assertStringEndsWith('.zip', $generator->getFullFilename());
    }

    public function testGenerateReport()
    {
        $volume = VolumeTest::create();

        $root = LabelTest::create();
        $child = LabelTest::create([
            'parent_id' => $root->id,
            'label_tree_id' => $root->label_tree_id,
        ]);

        $il = ImageLabelTest::create([
            'image_id' => ImageTest::create([
                'volume_id' => $volume->id,
                'filename' => 'foo.jpg',
            ])->id,
            'label_id' => $child->id,
        ]);

        $mock = Mockery::mock();

        $mock->shouldReceive('getPath')
            ->once()
            ->andReturn('abc');

        $mock->shouldReceive('put')
            ->once()
            ->with($this->columns);

        $mock->shouldReceive('put')
            ->once()
            ->with([
                $il->id,
                $il->image_id,
                $il->image->filename,
                null,
                null,
                $il->user_id,
                $il->user->firstname,
                $il->user->lastname,
                $il->label_id,
                $child->name,
                "{$root->name} > {$child->name}",
            ]);

        $mock->shouldReceive('close')
            ->once();

        App::singleton(CsvFile::class, function () use ($mock) {
            return $mock;
        });

        $mock = Mockery::mock();

        $mock->shouldReceive('open')
            ->once()
            ->andReturn(true);

        $mock->shouldReceive('addFile')->once();
        $mock->shouldReceive('close')->once();

        App::singleton(ZipArchive::class, function () use ($mock) {
            return $mock;
        });

        $generator = new CsvReportGenerator;
        $generator->setSource($volume);
        $generator->generateReport('my/path');
    }

    public function testGenerateReportSeparateLabelTrees()
    {
        $tree1 = LabelTreeTest::create(['name' => 'tree1']);
        $tree2 = LabelTreeTest::create(['name' => 'tree2']);

        $label1 = LabelTest::create(['label_tree_id' => $tree1->id]);
        $label2 = LabelTest::create(['label_tree_id' => $tree2->id]);

        $image = ImageTest::create();

        $il1 = ImageLabelTest::create([
            'image_id' => $image->id,
            'label_id' => $label1->id,
        ]);
        $il2 = ImageLabelTest::create([
            'image_id' => $image->id,
            'label_id' => $label2->id,
        ]);

        $mock = Mockery::mock();
        $mock->shouldReceive('getPath')
            ->twice()
            ->andReturn('abc', 'def');

        $mock->shouldReceive('put')
            ->twice()
            ->with($this->columns);

        $mock->shouldReceive('put')
            ->once()
            ->with([
                $il1->id,
                $image->id,
                $image->filename,
                null,
                null,
                $il1->user_id,
                $il1->user->firstname,
                $il1->user->lastname,
                $label1->id,
                $label1->name,
                $label1->name,
            ]);

        $mock->shouldReceive('put')
            ->once()
            ->with([
                $il2->id,
                $image->id,
                $image->filename,
                null,
                null,
                $il2->user_id,
                $il2->user->firstname,
                $il2->user->lastname,
                $label2->id,
                $label2->name,
                $label2->name,
            ]);

        $mock->shouldReceive('close')
            ->twice();

        App::singleton(CsvFile::class, function () use ($mock) {
            return $mock;
        });

        $mock = Mockery::mock();

        $mock->shouldReceive('open')
            ->once()
            ->andReturn(true);

        $mock->shouldReceive('addFile')
            ->once()
            ->with('abc', "{$tree1->id}-{$tree1->name}.csv");

        $mock->shouldReceive('addFile')
            ->once()
            ->with('def', "{$tree2->id}-{$tree2->name}.csv");

        $mock->shouldReceive('close')->once();

        App::singleton(ZipArchive::class, function () use ($mock) {
            return $mock;
        });

        $generator = new CsvReportGenerator([
            'separateLabelTrees' => true,
        ]);
        $generator->setSource($image->volume);
        $generator->generateReport('my/path');
    }
}
