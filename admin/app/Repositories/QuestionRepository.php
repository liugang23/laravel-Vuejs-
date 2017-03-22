<?php
namespace App\Repositories;

use App\Models\Question;
use App\Models\Topic;


class QuestionRepository
{
    /**
     * 获取话题
     * @param $id
     * @return mixed
     */
	public function getIdWithTopics($id)
	{
        // 使用 with 方法指定想要预载入的关联对象 预载入可以大大提高程序的性能
        // 这里的 topics 是App\Models\Question 中的 topics 方法
        return Question::where('id',$id)->with('topics')->first();
	}

	/**
	 * 添加
	 */
	public function addQuestion(array $attributes)
	{
		return Question::create($attributes);
	}

    /**
     * 查询话题
     */
    public function normalizeTopic(array $topics)
    {
        // 调用laravel自带的collect方法
        return collect($topics)->map(function ($topic) {
            if ( is_numeric($topic) ) {// 是否为数字
                // 如果存在 这里需要更新 increment用于递增
                // increment('votes', 5);加五
                Topic::find($topic)->increment('questions_count');
                return (int) $topic;
            }

            // 如果 $topic 不是数字 说明是用户新添加的 则在数据库中新建一个
            $newTopic = Topic::create(['name'=>$topic, 'questions_count'=>1]);
            // 返回主题id
            return $newTopic->id;
        })->toArray();
    }
}