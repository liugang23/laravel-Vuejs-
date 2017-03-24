<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['title', 'body', 'user_id'];

    /**
     * 定义多对多关系
     */
    public function topics()
    {
    	return $this->belongsToMany(Topic::class)->withTimestamps();
    }

    /**
     * 定义相对的关联
     * Eloquent 默认会使用 Question 数据库表的 user_id 字段查询关联。如果想要自己指定外键字段，可以在 belongsTo 方法里传入第二个参数
     */
    public function user()
    {
    	return $this->belongsTo('App\User');
    }

    /**
     * 定义回复
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
<<<<<<< HEAD
     * 定义多对多关系
=======
     * 定义用户-问题 关注多对多关系 
>>>>>>> 3a409d1fa3fe5da1c0a1d51a213d465604c2a349
     */
    public function followers()
    {
        return $this->belongsToMany('App\User', 'user_question')->withTimestamps();
    }

    /**
     * scope 前缀的模型方法
     * 范围查询可以让您轻松的重复利用模型的查询逻辑。要设定范围查询，只要定义有  scope 前缀的模型方法：
     */
    public function scopePublished($query)
    {
    	// 返回允许发布的内容
    	return $query->where('is_hidden', 'F');
    }

}
