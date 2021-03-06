<?php

use Biigle\Modules\Reports\Report;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameVideoSourceType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Report::where('source_type', 'Biigle\Modules\Videos\Video')
            ->update(['source_type' => 'Biigle\Video']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Report::where('source_type', 'Biigle\Video')
            ->update(['source_type' => 'Biigle\Modules\Videos\Video']);
    }
}
