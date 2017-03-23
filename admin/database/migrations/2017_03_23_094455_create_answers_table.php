<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->increments('id');
            // 记录回复者的用户id
            $table->integer('user_id')->index()->unsigned();
            // 回复与问题对应
            $table->integer('question_id')->index()->unsigned();
            // 回复的内容
            $table->text('body');
            // 点赞统计
            $table->integer('votes_count')->default(0);
            // 评论统计
            $table->integer('comments_count')->default(0); 
            // 是否发布   
            $table->string('is_hidden', 8)->default('F');
            // 是否关闭评论
            $table->string('close_comment', 8)->default('F');  
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
        Schema::dropIfExists('answers');
    }
}
