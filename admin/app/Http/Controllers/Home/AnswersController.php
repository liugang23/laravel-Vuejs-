<?php

namespace App\Http\Controllers\Home;

use App\Repositories\AnswerRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\AnswerRequest;
use Auth;

class AnswersController extends Controller
{
    protected $answer;

    public function __construct(AnswerRepository $answer)
    {
        $this->answer = $answer;
    }
    public function store(AnswerRequest $request, $question)
    {
        // 保存回复内容
    	$answer = $this->answer->addAnswer([
    	    'question_id' => $question,
            'user_id' => Auth::id(),
            'body' => $request->get('body')
        ]);
    	// 问题表单更新回复统计
        $answer->question()->increment('answers_count');
        return back();
    }
}
