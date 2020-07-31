<?php namespace Codalia\Journal\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('codalia_journal_groups', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
	    $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('codalia_journal_fields_groups', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('group_id')->unsigned();
            $table->integer('field_id')->unsigned();
            $table->primary(['field_id', 'group_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('codalia_journal_groups');
        Schema::dropIfExists('codalia_journal_fields_groups');
    }
}
