<?php
<<<<<<< HEAD

=======
>>>>>>> 3a409d1fa3fe5da1c0a1d51a213d465604c2a349
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
<<<<<<< HEAD
    protected $table = 'user_question';

=======
    // 定义表
    protected $table = 'user_question';

    // 定义白名单
>>>>>>> 3a409d1fa3fe5da1c0a1d51a213d465604c2a349
    protected $fillable = ['user_id', 'question_id'];
}
