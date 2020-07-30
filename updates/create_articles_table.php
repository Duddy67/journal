<?php namespace Codalia\Journal\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateArticlesTable extends Migration
{
    public function up()
    {
        Schema::create('codalia_journal_articles', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
	    $table->string('title')->nullable();
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->char('status', 15)->default('unpublished');
	    $table->integer('category_id')->unsigned()->nullable()->index();
            $table->char('field_group_id', 7)->nullable();
	    $table->integer('access_id')->unsigned()->nullable()->index();
	    $table->integer('created_by')->unsigned()->nullable()->index();
	    $table->integer('updated_by')->unsigned();
	    $table->timestamp('published_up')->nullable();
	    $table->timestamp('published_down')->nullable();
	    $table->integer('checked_out')->unsigned()->nullable()->index();
	    $table->timestamp('checked_out_time')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('codalia_journal_articles');
    }
}
