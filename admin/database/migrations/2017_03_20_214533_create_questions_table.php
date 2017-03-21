<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->increments('id');
            // 标题
            $table->string('title');
            // 内容
            $table->text('body');
            // 用户id
            $table->integer('user_id')->unsigned();
            // 评论统计
            $table->integer('comments_count')->default(0);
            // 问题发起统计
            $table->integer('followers_count')->default(1);
            // 回复统计
            $table->integer('answers_count')->default(0);
            // 是否关闭评论   F(false) 代表未关闭
            $table->string('close_comment', 8)->default('F');
            // 是否隐藏   F(false) 代表未隐藏
            $table->string('is_hidden', 8)->default('F');
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
        Schema::dropIfExists('questions');
    }
}
