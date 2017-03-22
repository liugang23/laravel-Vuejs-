<?php

namespace App\Http\Controllers\Home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionRequest;
use App\Repositories\QuestionRepository;
use Auth;


class QuestionsController extends Controller
{
    protected $questionRepository;

    /**
     * QuestionsController constructor
     */
    public function __construct(QuestionRepository $questionRepository)
    {   // except 表示 index、show 两个方法不受中间件影响
        $this->middleware('auth')->except(['index','show']);
        $this->questionRepository = $questionRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return 'index';
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('questions.make');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(QuestionRequest $request)
    {
        // 获取话题
        $topics = $this->questionRepository
                       ->normalizeTopic($request->get('topics'));

        // 获取数据
        $data = [
            'title' => $request->get('title'),
            'body' => $request->get('body'),
            'user_id' => Auth::id()
        ];

        // 写入数据库
        $question = $this->questionRepository->addQuestion($data);
        // 调用topics方法 attach 方法实现多对多关联将数据写入关联表
        $question->topics()->attach($topics);

        // 返回视图
        return redirect()->route('question.show',[$question->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $question = $this->questionRepository->getIdWithTopics($id);

        // compact 创建一个包含变量名和它们的值的数组
        return view('questions.show',compact('question'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $question = $this->questionRepository->getQuestion($id);
        // 判断操作方是否是问题的发布者
        if (Auth::user()->owns($question)) {
            return view('questions.edit', compact('question'));
        }
        // 如果不是 跳转
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(QuestionRequest $request, $id)
    {
        $question = $this->questionRepository->getQuestion($id);
        // 获取话题
        $topics = $this->questionRepository
                       ->normalizeTopic($request->get('topics'));
        // update 批次更新模型
        $question->update([
            'title' => $request->get('title'),
            'body' => $request->get('body'),
        ]);
        // Sync 方法同时附加一个以上多对多关联
        $question->topics()->sync($topics);
        return redirect()->route('question.show', [$question->id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


}
