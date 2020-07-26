<?php namespace Codalia\Journal\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateMultiValuesTable extends Migration
{
    public function up()
    {
        Schema::create('codalia_journal_multi_values', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
	    $table->integer('extra_field_id')->unsigned()->nullable()->index();
	    $table->string('value')->nullable();
	    $table->string('text')->nullable();
	    $table->integer('ordering')->unsigned();
        });
    }

    public function down()
    {
        Schema::dropIfExists('codalia_journal_multi_values');
    }
}
