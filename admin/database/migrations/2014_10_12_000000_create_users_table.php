<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('email')->unique();
            $table->string('password');
            // 用户头像
            $table->string('avatar');
            // 邮箱验证 token
            $table->string('confirmation_token');
            // 用户邮箱激活状态
            $table->smallInteger('is_active')->default(0);
            // 发表统计
            $table->integer('questions_count')->default(0);
            // 回答统计
            $table->integer('answers_count')->default(0);
            // 评论统计
            $table->integer('comments_count')->default(0);
            // 收藏统计
            $table->integer('favorites_count')->default(0);
            // 点赞
            $table->integer('likes_count')->default(0);
            // 关注
            $table->integer('followers_count')->default(0);
            // 被关注
            $table->integer('followings_count')->default(0);
            // 编辑个人资料 使用laravel 中的json方法
            $table->json('settings')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
