<?php

namespace App\Http\Controllers\Home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Topic;
use App\Http\Requests\QuestionRequest;
use Auth;


class QuestionsController extends Controller
{
    public function __construct()
    {   // except 表示 index、show 两个方法不受中间件影响
        $this->middleware('auth')->except(['index','show']);
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
        $topics = $this->normalizeTopic($request->get('topics'));

        // 获取数据
        $data = [
            'title' => $request->get('title'),
            'body' => $request->get('body'),
            'user_id' => Auth::id()
        ];

        // 写入数据库
        $question = Question::create($data);
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
        // 使用 with 方法指定想要预载入的关联对象 预载入可以大大提高程序的性能
        // 这里的 topics 是App\Models\Question 中的 topics 方法
        $question = Question::where('id',$id)->with('topics')->first();

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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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

    /**
     * 获取话题
     */
    private function normalizeTopic(array $topics)
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
