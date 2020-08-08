<?php namespace Codalia\Journal\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateFieldValuesTable extends Migration
{
    public function up()
    {
        Schema::create('codalia_journal_field_values', function (Blueprint $table) {
            $table->engine = 'InnoDB';
	    $table->integer('field_id')->unsigned()->index();
	    $table->integer('article_id')->unsigned()->index();
	    $table->string('value')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('codalia_journal_field_values');
    }
}
