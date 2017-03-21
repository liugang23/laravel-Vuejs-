<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['title', 'body', 'user_id'];

    /**
     * 定义多对多关系
     */
    public function topics()
    {
    	return $this->belogsToMany(Topic::class)->withTimestamps();
    }
}
