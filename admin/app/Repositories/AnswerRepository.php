<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017-03-23
 * Time: 18:52
 */

namespace App\Repositories;


use App\Models\Answer;

class AnswerRepository
{
    /**
     *  添加回复
     */
    public function addAnswer(array $attributes)
    {
        return Answer::create($attributes);
    }

}