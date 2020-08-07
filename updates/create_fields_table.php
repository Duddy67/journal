<?php namespace Codalia\Journal\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateFieldsTable extends Migration
{
    public function up()
    {
        Schema::create('codalia_journal_fields', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
	    $table->string('name')->nullable();
            $table->string('code')->index();
            $table->char('type', 25)->index();
            $table->char('status', 15)->default('unpublished');
	    $table->boolean('required');
	    $table->string('default_value')->nullable();
	    $table->integer('checked_out')->unsigned()->nullable()->index();
	    $table->timestamp('checked_out_time')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('codalia_journal_fields');
    }
}
