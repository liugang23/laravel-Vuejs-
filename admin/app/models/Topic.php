<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $fillable = ['name', 'questions_count', 'bio'];

    /**
     * 定义多对多关系
     */
    public function questions()
    {
    	return $this->belogsToMany(Question::class)->withTimestamps();
    }
}
