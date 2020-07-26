<?php namespace Codalia\Journal\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateOrderingsTable extends Migration
{
    public function up()
    {
        Schema::create('codalia_journal_orderings', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->string('id', 25);
	    $table->integer('category_id')->unsigned()->nullable()->index();
	    $table->integer('article_id')->unsigned()->nullable()->index();
	    $table->string('title')->nullable();
	    $table->integer('sort_order')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('codalia_journal_orderings');
    }
}
