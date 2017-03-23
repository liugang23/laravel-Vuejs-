<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = ['user_id', 'question_id', 'body'];

    /* 定义一对多 */
    public function user()
    {
    	return $this->belongsTo('App\User');
    }

    /*  */
    public function question()
    {
    	return $this->belongsTo(Question::class);
    }
}
