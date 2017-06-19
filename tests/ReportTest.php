<?php

namespace Biigle\Tests\Modules\Export;

use File;
use Mockery;
use ModelTestCase;
use Biigle\Tests\VolumeTest;
use Biigle\Tests\ProjectTest;
use Biigle\Modules\Export\Report;
use Biigle\Modules\Export\Support\Reports\ReportGenerator;

class ReportTest extends ModelTestCase
{
    /**
     * The model class this class will test.
     */
    protected static $modelClass = Report::class;

    public function testAttributes()
    {
        $this->assertNotNull($this->model->user_id);
        $this->assertNotNull($this->model->type_id);
        $this->assertNotNull($this->model->source_id);
        $this->assertNotNull($this->model->source_type);
        $this->assertNotNull($this->model->created_at);
        $this->assertNotNull($this->model->updated_at);
        $this->assertNotNull($this->model->source_name);
        $this->assertNotNull($this->model->name);
        $this->assertNotNull($this->model->filename);
    }

    public function testCastsOptions()
    {
        $this->model->options = ['a' => true];
        $this->model->save();
        $this->assertEquals(['a' => true], $this->model->fresh()->options);
    }

    public function testGenerate()
    {
        $id = $this->model->id;
        $path = config('export.reports_storage');

        $mock = Mockery::mock(ReportGenerator::class);
        $mock->shouldReceive('generate')->once()->with("{$path}/{$id}");

        $this->model->setReportGenerator($mock);
        $this->model->generate();
    }

    public function testGenerateSourceDeleted()
    {
        $this->model->source()->delete();
        $this->setExpectedException(\Exception::class);
        $this->model->fresh()->generate();
    }

    public function testGetReportGeneratorSourceDeleted()
    {
        $this->model->source()->delete();
        $this->assertNull($this->model->fresh()->getReportGenerator());
    }

    public function testSourceName()
    {
        $source = VolumeTest::create();
        $this->model->source()->associate($source);
        $this->assertEquals($source->name, $this->model->source_name);
        $source->delete();
        $this->assertEquals($source->name, $this->model->source_name);
    }

    public function testGetSubjectAttribute()
    {
        $volume = VolumeTest::create();
        $this->model->source()->associate($volume);
        $this->assertEquals('volume '.$volume->name, $this->model->subject);

        $project = ProjectTest::create();
        $this->model->source()->associate($project);
        $this->assertEquals('project '.$project->name, $this->model->subject);
    }

    public function testGetSubjectAttributeSourceDeleted()
    {
        $subject = $this->model->subject;
        $this->model->source()->delete();
        $this->assertEquals($subject, $this->model->fresh()->subject);
    }

    public function testGetUrl()
    {
        $this->assertStringEndsWith('reports/'.$this->model->id, $this->model->getUrl());
    }

    public function testObserveSelf()
    {
        File::shouldReceive('delete')->once()->with($this->model->getPath());
        $this->model->delete();
    }

    public function testObserveUser()
    {
        File::shouldReceive('delete')->once()->with([$this->model->getPath()]);
        $this->model->user->delete();
        $this->assertNull($this->model->fresh());
    }

    public function testDontObserveProjects()
    {
        $project = ProjectTest::create();
        $this->model->source()->associate($project);
        $this->model->save();

        $this->assertNotNull($this->model->fresh()->source);
        $project->delete();
        $this->assertNull($this->model->fresh()->source);
        $this->assertNotNull($this->model->fresh()->source_id);
        $this->assertNotNull($this->model->fresh()->source_type);
        $this->assertEquals($project->name, $this->model->fresh()->source_name);
    }

    public function testObserveVolumes()
    {
        $volume = VolumeTest::create();
        $this->model->source()->associate($volume);
        $this->model->save();

        $this->assertNotNull($this->model->fresh()->source);
        $volume->delete();
        $this->assertNull($this->model->fresh()->source);
        $this->assertNotNull($this->model->fresh()->source_id);
        $this->assertNotNull($this->model->fresh()->source_type);
        $this->assertEquals($volume->name, $this->model->fresh()->source_name);
    }
}
