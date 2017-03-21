<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   // 话题标签
        Schema::create('topics', function (Blueprint $table) {
            $table->increments('id');
            // 话题的名字
            $table->string('name');
            // 话题简介 允许为空
            $table->text('bio')->nullable();
            // 问题统计
            $table->integer('questions_count')->default(0);
            // 关注统计
            $table->integer('followers_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('topics');
    }
}
