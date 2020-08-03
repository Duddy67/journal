<?php namespace Codalia\Journal\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateOptionsTable extends Migration
{
    public function up()
    {
        Schema::create('codalia_journal_options', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id');
	    $table->integer('field_id')->unsigned()->nullable()->index();
	    $table->string('value')->nullable();
	    $table->string('text')->nullable();
	    $table->integer('ordering')->unsigned();
        });
    }

    public function down()
    {
        Schema::dropIfExists('codalia_journal_options');
    }
}
