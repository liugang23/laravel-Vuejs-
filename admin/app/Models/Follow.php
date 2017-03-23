<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    // 定义表
    protected $table = 'user_question';

    // 定义白名单
    protected $fillable = ['user_id', 'question_id'];
}
