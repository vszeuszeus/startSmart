<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableGroupPrivilege extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_privilege', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('privilege_id')->unsigned();
            $table->integer('group_id')->unsigned();
            $table->timestamps();

            $table->foreign('privilege_id')
                ->references('id')
                ->on('privileges')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('group_id')
                ->references('id')
                ->on('groups')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('group_privilege', function(Blueprint $table) {
            $table->dropForeign('group_privilege_group_id_foreign');
            $table->dropForeign('group_privilege_privilege_id_foreign');
        });
        Schema::dropIfExists('group_privilege');
    }
}
