# laravel+Vuejs 知乎

#### 创建 user 表模型
	* 新建user 表

	public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('email')->unique();
            $table->string('password');
            // 用户头像
            $table->string('avatar');
            // 邮箱验证 token
            $table->string('confirmation_token');
            // 用户邮箱激活状态
            $table->smallInteger('is_active')->default(0);
            // 发表统计
            $table->integer('questions_count')->default(0);
            // 回答统计
            $table->integer('answers_count')->default(0);
            // 评论统计
            $table->integer('comments_count')->default(0);
            // 收藏统计
            $table->integer('favorites_count')->default(0);
            // 点赞
            $table->integer('likes_count')->default(0);
            // 关注
            $table->integer('followers_count')->default(0);
            // 被关注
            $table->integer('followings_count')->default(0);
            // 编辑个人资料 使用laravel 中的json方法
            $table->json('settings')->nullabel();
            $table->rememberToken();
            $table->timestamps();
        });
    }

	// 执行创建user 表
	php artisan migrate

	// 执行 php artisan make:auth  生成注册、登录视图、认证路由及HomeController
    www.zt.com/login     访问登录
    www.zt.com/register  访问注册

#### 这里尝试二种邮件发送方式
__首先安装相关依赖包__

* 安装Guzzle Guzzle是一个PHP HTTP 客户端和框架，用于构建 RESTful web service 客户端。
    composer require guzzlehttp/guzzle

* 安装 sendcloud
    composer require naux/sendcloud

[https://github.com/NauxLiu/Laravel-SendCloud](https://github.com/NauxLiu/Laravel-SendCloud)
    
__1、使用sendcloud 模板发送邮件服务__
    * 安装完成，开始配置
    app\config 目录下 app.php 文件中添加注册服务
    'providers' => [
        Naux\Mail\SendCloudServiceProvider::class,
    ]

    * 在 .env 中配置你的密钥， 并修改邮件驱动为 sendcloud
    MAIL_DRIVER=sendcloud

    SEND_CLOUD_USER=   # 创建的 api_user
    SEND_CLOUD_KEY=    # 分配的 api_key


* 2、使用laravle 自带的mail发送邮件

    

#### 注册、邮件发送

	* 创建注册控制器
    * app\Http\Controllers\Auth 目录下 RegisterController.php

    <?php

    namespace App\Http\Controllers\Auth;

    use App\User;
    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Foundation\Auth\RegistersUsers;
    use App\Tools\EMAILResult;
    use Naux\Mail\SendCloudTemplate;
    use Mail;

    class RegisterController extends Controller
    {
        use RegistersUsers;

        /**
         * Where to redirect users after registration.
         *
         * @var string
         */
        protected $redirectTo = '/home';

        /**
         * Create a new controller instance.
         *
         * @return void
         */
        public function __construct()
        {
            $this->middleware('guest');
        }

        /**
         * Get a validator for an incoming registration request.
         *
         * @param  array  $data
         * @return \Illuminate\Contracts\Validation\Validator
         */
        protected function validator(array $data)
        {
            return Validator::make($data, [
                'name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|min:6|confirmed',
            ]);
        }

        /**
         * Create a new user instance after a valid registration.
         *
         * @param  array  $data
         * @return User
         */
        protected function create(array $data)
        {
            // 判断邮箱是否存在

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                // 用户默认用户头像
                'avatar' => '/images/avatart/user.png', // 头像目录
                // 邮箱验证 token
                'confirmation_token' => str_random(50), // 使用laravel 提供的函数生成50位的字串
                'password' => bcrypt($data['password']),// 密码加密
            ]);

            // 邮件发送方法一 自定义发送邮件
            $this->emailSendToVerify($user);
            // 邮件发送方法二 调用sendcloud 模板
            // $this->sendVerifyEmailTo($user);
            return $user;
        }

        private function emailSendToVerify($user)
        {
            // 邮件内容
            $EMAILResult = new EMAILResult();
            $EMAILResult->to = $user->email;
            $EMAILResult->cc = '3434744@qq.com';
            $EMAILResult->subject = '邮箱激活认证';
            $EMAILResult->content = '请于24小时内点击该链接完成验证. http://www.zt.com/email/verify/'.$user->confirmation_token;
            // 发送邮件
            Mail::send('email_register', ['EMAILResult' => $EMAILResult], function($m) use ($EMAILResult) {
                $m->to($EMAILResult->to, '尊敬的用户')
                ->cc($EMAILResult->cc)
                ->subject($EMAILResult->subject);
            });

        }

        private function sendVerifyEmailTo($user)
        {
            $data = [
                'url'=>route('email.verify',['token'=>$user->confirmation_token]),
                'name'=>$user->name
            ];
            // 选择模板
            $template = new SendCloudTemplate('test_template', $data);
            // 发送邮件
            Mail::raw($template, function ($message) use ($user) {
                // 邮件发送者
                $message->from('3434744@qq.com', '幸福号'); 
                // 邮件接收者
                $message->to($user->email);
            });
            
        }
    }


#### 登录
__首先安装提示插件__

[https://github.com/laracasts/flash](https://github.com/laracasts/flash)
    composer require laracasts/flash
__配置插件__
    
    app\config 目录下 app.php 文件中添加注册服务
    'providers' => [
        Laracasts\Flash\FlashServiceProvider::class,
    ];

__注册__

    <?php

    namespace App\Http\Controllers\Auth;

    use App\Http\Controllers\Controller;
    use Illuminate\Foundation\Auth\AuthenticatesUsers;
    use Illuminate\Http\Request;

    class LoginController extends Controller
    {
        // 引入login 方法
        use AuthenticatesUsers;

        /**
         * Where to redirect users after login.
         *
         * @var string
         */
        protected $redirectTo = '/home';

        /**
         * Create a new controller instance.
         *
         * @return void
         */
        public function __construct()
        {
            $this->middleware('guest', ['except' => 'logout']);
        }

        /**
         * 重写 login 方法
         */
        public function login(Request $request)
        {
            $this->validateLogin($request);

            // If the class is using the ThrottlesLogins trait, we can automatically throttle
            // the login attempts for this application. We'll key this by the username and
            // the IP address of the client making these requests into this application.
            if ($this->hasTooManyLoginAttempts($request)) {
                $this->fireLockoutEvent($request);

                return $this->sendLockoutResponse($request);
            }

            if ($this->attemptLogin($request)) {
                flash('欢迎回来！', 'success');
                return $this->sendLoginResponse($request);
            }

            // If the login attempt was unsuccessful we will increment the number of attempts
            // to login and redirect the user back to the login form. Of course, when this
            // user surpasses their maximum number of attempts they will get locked out.
            $this->incrementLoginAttempts($request);

            return $this->sendFailedLoginResponse($request);
        }

        /**
         * Attempt to log the user into the application.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return bool
         */
        protected function attemptLogin(Request $request)
        {
            // array_merge 将两个数组合并为一个数组
            $credentials = array_merge($this->credentials($request), ['is_active'=>1]);
            return $this->guard()->attempt(
                $credentials, $request->has('remember')
            );
        }

    }

#### 本地化和自定义消息
    * laravel 的自定义消息为英文，
    * 验证提示需要在 resources/lang/en/validation.php 文件里修改

    'custom' => [
        'email' => [
            'unique' => '邮箱已被占用！',
        ],
        'password' => [
            'confirmed' => '两次密码不符！',
        ],
    ],
    
    * 重置密码需要在 resources/lang/en/passwords.php 文件里修改

    'password' => 'Passwords must be at least six characters and match the confirmation.',
    'reset' => 'Your password has been reset!',
    'sent' => '重置密码邮件已经成功发送!',
    'token' => 'This password reset token is invalid.',
    'user' => "没有找到对应的邮箱信息.",

#### 问题发布

* 创建 Question model

    php artisan make:model models/Question -m

* 配置 Question 表

    <?php

    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;

    class CreateQuestionsTable extends Migration
    {
        public function up()
        {
            Schema::create('questions', function (Blueprint $table) {
                $table->increments('id');
                // 标题
                $table->string('title');
                // 内容
                $table->text('body');
                // 用户id
                $table->integer('user_id')->unsigned();
                // 评论统计
                $table->integer('comments_count')->default(0);
                // 问题发起统计
                $table->integer('followers_count')->default(1);
                // 回复统计
                $table->integer('answers_count')->default(0);
                // 是否关闭评论   F(false) 代表未关闭
                $table->string('close_comment', 8)->default('F');
                // 是否隐藏   F(false) 代表未隐藏
                $table->string('is_hidden', 8)->default('F');
                $table->timestamps();
            });
        }
    }

* 生成数据库 Question 表

    php artisan migrate 

    

__安装编辑插件__
[https://github.com/overtrue/laravel-ueditor](https://github.com/overtrue/laravel-ueditor)
    
    composer require "overtrue/laravel-ueditor:~1.0"

    // app\config 目录下 app.php 文件中添加注册服务
    'providers' => [
        Overtrue\LaravelUEditor\UEditorServiceProvider::class,
    ];

    // 发布配置文件与资源
    php artisan vendor:publish

    // 模板引入编辑器

    // 这行的作用是引入编辑器需要的 css,js 等文件，所以你不需要再手动去引入它们。

    @include('vendor.ueditor.assets')

    // 编辑器的初始化

    <!-- 实例化编辑器 -->
    <script type="text/javascript">
        var ue = UE.getEditor('container');
        ue.ready(function() {
            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}'); // 设置 CSRF token.
        });
    </script>

    <!-- 编辑器容器 -->
    <script id="container" name="content" type="text/plain"></script>


#### 创建发布问题控制器
	// 创建控制器
    php artisan make:controller Home\\QuestionsController --resource
    // 查看路由
    php artisan route:list

    * 验证方法一：
    // 编辑控制器
    <?php

	namespace App\Http\Controllers\Home;
	
	use Illuminate\Http\Request;
	use App\Http\Controllers\Controller;
	use App\models\Question;
	use App\Http\Requests\QuestionRequest;
	use Auth;
	
	
	class QuestionsController extends Controller
	{
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
	    public function store(Request $request)
	    {
	        // 定义验证规则
	        $rules = [
	            'title' => 'required|min:6|max:196',
	            'body' => 'required|min:26',
	        ];
	        // 自定义消息
	        $message = [
	            'title.required' => '标题不能为空',
	            'title.min' => '标题不能少于6个字',
	            'body.required' => '内容不能为空',
	            'body.min' => '内容不能少于26个字',
	        ];
	        // 对用户提交数据进行验证
	        $this->validate($request, $rules, $message);
	        $data = [
	            'title' => $request->get('title'),
	            'body' => $request->get('body'),
	            'user_id' => Auth::id()
	        ];
	
	
	        $question = Question::create($data);
	
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
	        $question = Question::find($id);
	
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
	}


	* 方法二：
	// 方法一中，验证规则直接写在控制器中，这样代码显得比较臃肿。
	// 在方法二中，增加了对发布问题请求验证
	// 创建发布问题请求 验证
	php artisan make:request QuestionRequest

	// QuestionRequest.php
	<?php
	
	namespace App\Http\Requests;
	
	use Illuminate\Foundation\Http\FormRequest;
	
	class QuestionRequest extends FormRequest
	{
	    /**
	     * Determine if the user is authorized to make this request.
	     *
	     * @return bool
	     */
	    public function authorize()
	    {
	        // true 允许每个都可以发布问题
	        return true;
	    }
	
	    /**
	     * 自定义消息提示
	     */
	    public function messages()
	    {
	        return [
	            'title.required' => '标题不能为空',
	            'title.min' => '标题不能少于6个字',
	            'body.required' => '内容不能为空',
	            'body.min' => '内容不能少于26个字',
	        ];
	    }
	
	    /**
	     * Get the validation rules that apply to the request.
	     * 对请求进行验证
	     * @return array
	     */
	    public function rules()
	    {
	        return [
	            'title' => 'required|min:6|max:196',
	            'body' => 'required|min:26',
	        ];
	    }
	}

	* 对 QuestionsController.php 控制器 store部分进行修改

	public function store(QuestionRequest $request)
    {
        $data = [
            'title' => $request->get('title'),
            'body' => $request->get('body'),
            'user_id' => Auth::id()
        ];

        $question = Question::create($data);

        return redirect()->route('question.show',[$question->id]);
    }

#### 美化编辑器

[https://github.com/JellyBool/simple-ueditor](https://github.com/JellyBool/simple-ueditor)

    * clone 代码
    git clone https://github.com/JellyBool/simple-ueditor.git

    * 用此项目的 ueditor 目录替换原来的 ueditor 目录

    * 实例化编辑器的时候配置 toolbar ，主要是 toolbar 的配置

    var ue = UE.getEditor('editor', {
        toolbars: [
                ['bold', 'italic', 'underline', 'strikethrough', 'blockquote', 'insertunorderedlist', 'insertorderedlist', 'justifyleft','justifycenter', 'justifyright',  'link', 'insertimage', 'fullscreen']
            ],
        elementPathEnabled: false,
        enableContextMenu: false,
        autoClearEmptyNode:true,
        wordCount:false,
        imagePopup:false,
        autotypeset:{ indent: true,imageBlockLine: 'center' }
    });

[文档说明](fex.baidu.com/ueditor/#start-config)

#### 访问限制

    * 同一控制器里有的方法允许访问，有的需要受限制。
    * 这里我们在 QuestionsController 控制器里添加构造方法
    * 通过这个构造方法实现用户在创建问题前需要先登录的功能

    class QuestionsController extends Controller
    {
        public function __construct()
        {   // except 表示 index、show 两个方法不受中间件影响
            $this->middleware('auth')->except(['index','show']);
        }

    }

#### 为问题添加话题
    // 生成标签model 和相应的数据库表
    php artisan make:model models\\Topic -m

    // 生成 questions、topics 多对多关联关系表
    php artisan make:migration create_questions_topics_table --create=question_topic

    // 创建数据库表
    php artisan migrate

    // 添加多对多关系
    class Topic extends Model
    {
        protected $fillable = ['name', 'questions_count'];

        /**
         * 定义多对多关系
         */
        public function questions()
        {
            return $this->belogsToMany(Question::class)->withTimestamps();
        }
    }


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

[http://select2.github.io/](http://select2.github.io/)
    // 添加 select2 到本地实现话题标签
    * 首先下载select2 相关样式文件
    * 添加select2 样式文件方法有两种
    * 第一种：(此方法目前报错，具体问题待查)
        // 1、进入到相应的文件夹目录下
        cd resources/assets
        // 2、查看目录情况
        ls
        // 3、如果没有css文件夹，新建一个
        mkdir css
        // 4、进入到 css 文件夹下
        cd css
        // 5、添加 css 样式下载路径并下载
        wget https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css
        // 6、进入 js 文件夹 添加 js 样式下载路径并下载
        wget https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js
        // 7、安装完成后 执行命令安装相应的包
        npm install
        // 8、配置select2样式文件
        在 resources/assets/js 目录下 bootstrap.js 文件中添加
        window.$ = window.jQuery = require('jquery');
        require('bootstrap-sass');
        require('./select2.min');

        在 resources/assets/sass 目录下 app.scss 文件中添加
        @import "./../css/select2.min";

        // 执行 gulp

    // 在gulpfile.js 文件中添加 加载样式路径 压缩样式文件、避免浏览器缓存
    elixir((mix) => {
        mix.sass('app.scss')
           .webpack('app.js');

        mix.version(['js/app.js', 'css/app.css'])
    });

    // 在 resources/views/layouts 目录下 app.blade.php 文件中添加
    <link href="{{ elixir('css/app.css') }}" rel="stylesheet">
    <script src="{{ elixir('js/app.js') }}"></script>

    // 执行 gulp 命令
    // 在 resources/views/layouts 目录下 app.blade.php 文件中添加 @yield('js')
    // @yield('js') 相关代码在 resources/views/questions 目录下 make.blade.php 中

    * 第二种方法直接将select2 的样式文件放到public相应目录下并在app.blade.php 文件中引入

    // 调试多选功能
        * 添加多选php 代码
        <select class="js-example-basic-multiple" multiple="multiple">
            <option value="AL">Alabama</option>
                ...
            <option value="WY">Wyoming</option>
        </select>

        * 添加多选js 代码
        <script type="text/javascript">
            $(".js-example-basic-multiple").select2();
        </script>

        // resources/views/questions/make 具体示例：
        @extends('layouts.app')

        @section('content')
        @include('vendor.ueditor.assets')
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="panel panel-default">
                        <div class="panel-heading">发布问题</div>
                        <div class="panel-body">
                            <form action="/questions" method="post">
                                {!! csrf_field() !!}
                                <div class="form-group{{ $errors->has('title') ? 'has-error' : '' }}">
                                    <label for="title">标 题</label>
                                    <input type="text" value="{{ old('title') }}" name="title" class="form-control" placeholder="标题" id="title">
                                    @if ($errors->has('title'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('title') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <select class="js-example-basic-multiple" multiple="multiple">
                                        <option value="AL">Alabama</option>
                                        <option value="WY">Wyoming</option>
                                    </select>
                                </div>
                                <div class="form-group{{ $errors->has('body') ? 'has-error' : '' }}">
                                    <label for="body">描述</label>
                                    <!-- 编辑器容器 -->
                                    <!-- 非转义可能引起攻击,需要过滤 -->
                                    <script id="container" name="body" type="text/plain" style="height:200px;">
                                        {!! old('body') !!}
                                    </script>
                                    @if ($errors->has('body'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('body') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <button class="btn btn-success pull-right" type="submit">发布问题</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @section('js')
        <!-- 实例化编辑器 -->
        <script type="text/javascript">
            var ue = UE.getEditor('container', {
                toolbars: [
                        ['bold', 'italic', 'underline', 'strikethrough', 'blockquote', 'insertunorderedlist', 'insertorderedlist', 'justifyleft','justifycenter', 'justifyright',  'link', 'insertimage', 'fullscreen']
                    ],
                elementPathEnabled: false,
                enableContextMenu: false,
                autoClearEmptyNode:true,
                wordCount:false,
                imagePopup:false,
                autotypeset:{ indent: true,imageBlockLine: 'center' }
            });
            ue.ready(function() {
                ue.execCommand('serverparam', '_token', '{{ csrf_token() }}'); // 设置 CSRF token.
            });

            $(document).ready(function() {
                $(".js-example-basic-multiple").select2();
            });
        </script>
        @endsection

        @endsection


#### 调试话题功能
    * 生成话题测试数据
    $factory->define(App\Topic::class, function (Faker\Generator $faker) {

        return [
            'name' => $faker->word,
            'bio' => $faker->paragraph,
            'questions_count' => 1
        ];
    });

    * 定义话题 model
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

    * 执行命令生成测试数据
        1、首先执行 php artisan tinker
[php artisan tinker](http://www.tuicool.com/articles/RVfuIjE)
    
        2、执行命令 生成10条测试数据
        执行 factory(App\Models\Topic::class,10)->make()

        3、执行命令 填充数据
        执行 factory(App\Models\Topic::class,10)->create()

        4、修改 routes/api 路由
        Route::get('/topics', function (Request $request) {
            $topics = \App\Models\topic::select(['id', 'name'])
                    ->where('name', 'like', '%'.$request->query('q').'%')
                    ->get();
            return $topics;
        })->middleware('api');

        5、浏览器中输入 http://www.zt.com/api/topics?q=laravel 查看返回结果

    * resources/questions/make 添加ajax方法实现数据实时模糊查询

    @extends('layouts.app')

    @section('content')
    @include('vendor.ueditor.assets')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">发布问题</div>
                    <div class="panel-body">
                        <form action="/questions" method="post">
                            {!! csrf_field() !!}
                            <div class="form-group{{ $errors->has('title') ? 'has-error' : '' }}">
                                <label for="title">标 题</label>
                                <input type="text" value="{{ old('title') }}" name="title" class="form-control" placeholder="标题" id="title">
                                @if ($errors->has('title'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('title') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group">
                                <select class="js-example-placeholder-multiple js-data-example-ajax form-control" multiple="multiple"></select>
                            </div>
                            <div class="form-group{{ $errors->has('body') ? 'has-error' : '' }}">
                                <label for="body">描述</label>
                                <!-- 编辑器容器 -->
                                <!-- 非转义可能引起攻击,需要过滤 -->
                                <script id="container" name="body" type="text/plain" style="height:200px;">
                                    {!! old('body') !!}
                                </script>
                                @if ($errors->has('body'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('body') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <button class="btn btn-success pull-right" type="submit">发布问题</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('js')
    <!-- 实例化编辑器 -->
    <script type="text/javascript">
        var ue = UE.getEditor('container', {
            toolbars: [
                    ['bold', 'italic', 'underline', 'strikethrough', 'blockquote', 'insertunorderedlist', 'insertorderedlist', 'justifyleft','justifycenter', 'justifyright',  'link', 'insertimage', 'fullscreen']
                ],
            elementPathEnabled: false,
            enableContextMenu: false,
            autoClearEmptyNode:true,
            wordCount:false,
            imagePopup:false,
            autotypeset:{ indent: true,imageBlockLine: 'center' }
        });
        ue.ready(function() {
            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}'); // 设置 CSRF token.
        });

        $(document).ready(function() {
            function formatTopic (topic) {
                return "<div class='select2-result-repository clearfix'>"+
                "<div class='select2-result-repository__meta'>" +
                "<div class='select2-result-repository__title'>" +
                topic.name ? topic.name : "Laravel" +
                "<div></div></div>";
            }

            function formatTopicSelection (topic) {
                // 这里的name是后端返回的  如果后端没有返回name，这里就显示用户输入的text
                return topic.name || topic.text;
            }

            $(".js-example-placeholder-multiple").select2({
                tags: true,
                placeholder: '选择相关话题',
                minimumInputLength: 2,
                ajax: {
                    url:'/api/topics',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function (data, params) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                templateResult: formatTopic,// 返回样式
                templateSelection: formatTopicSelection,// 返回样式
                escapeMarkup: function (markup) { return markup; }
            });
        });
    </script>
    @endsection

    @endsection

#### 实现选择话题整个流程
    * Question.php 中添加 topics 方法，实现多对多关系
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
    }

    * QuestionsController 控制器增加方法完善选择话题流程

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

    * resources/views/questions/show.blade.php 修改

    @extends('layouts.app')

    @section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ $question->title }}
                        <span style="margin-left:10px">
                        @foreach($question->topics as $topic)
                            <a class="topic" href="/topic/{{ $topic->id }}">{{ $topic->name }}</a>
                        @endforeach
                        </span>
                    </div>

                    <div class="panel-body">
                        {!! $question->body !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .panel-body img { width: 100%;}
        a.topic {
            background-color: #eff6fa;
            padding: 1px 10px 0;
            border-radius: 30px;
            text-decoration: none;
            margin: 0 5px 5px 0;
            display: inline-block;
            white-space: nowrap;
            cursor: pointer;
        }
        a.topic:hover {
            background: #259;
            color: #fff;
            text-decoration: none;
        }

    </style>
    @endsection

#### 使用Repository模式 实现控制器与model分离

    * 新建 Repositories 目录 创建 QuestionRepository.php

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
         * 添加问题
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

    * 优化 QuestionsController 控制器，实现model与控制器分离

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

    }

#### 实现问题编辑

    * QuestionsController 控制器
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

    }

    * Repositories/QuestionRepository.php
    <?php
    namespace App\Repositories;

    use App\Models\Question;
    use App\Models\Topic;


    class QuestionRepository
    {
        /**
         * 获取问题
         */
        public function getQuestion($id)
        {
            return Question::find($id);   
        }
    }

    * resources/views/questions/edit.blade.php
    @extends('layouts.app')

    @section('content')
    @include('vendor.ueditor.assets')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">编辑问题</div>
                    <div class="panel-body">
                        <form action="/questions/{{ $question->id }}" method="post">
                            {{ method_field('PATCH') }}
                            {!! csrf_field() !!}
                            <div class="form-group{{ $errors->has('title') ? 'has-error' : '' }}">
                                <label for="title">标 题</label>
                                <input type="text" value="{{ $question->title }}" name="title" class="form-control" placeholder="标题" id="title">
                                @if ($errors->has('title'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('title') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group">
                                <select name="topics[]" class="js-example-placeholder-multiple js-data-example-ajax form-control" multiple="multiple">
                                    @foreach($question->topics as $topic)
                                        <option value="{{ $topic->id }}" selected="selected">{{ $topic->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group{{ $errors->has('body') ? 'has-error' : '' }}">
                                <label for="body">描述</label>
                                <!-- 编辑器容器 -->
                                <!-- 非转义可能引起攻击,需要过滤 -->
                                <script id="container" name="body" type="text/plain" style="height:200px;">
                                    {!! $question->body !!}
                                </script>
                                @if ($errors->has('body'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('body') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <button class="btn btn-success pull-right" type="submit">发布问题</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('js')
    <!-- 实例化编辑器 -->
    <script type="text/javascript">
        var ue = UE.getEditor('container', {
            toolbars: [
                    ['bold', 'italic', 'underline', 'strikethrough', 'blockquote', 'insertunorderedlist', 'insertorderedlist', 'justifyleft','justifycenter', 'justifyright',  'link', 'insertimage', 'fullscreen']
                ],
            elementPathEnabled: false,
            enableContextMenu: false,
            autoClearEmptyNode:true,
            wordCount:false,
            imagePopup:false,
            autotypeset:{ indent: true,imageBlockLine: 'center' }
        });
        ue.ready(function() {
            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}'); // 设置 CSRF token.
        });

        $(document).ready(function() {
            function formatTopic (topic) {
                return "<div class='select2-result-repository clearfix'>"+
                "<div class='select2-result-repository__meta'>" +
                "<div class='select2-result-repository__title'>" +
                topic.name ? topic.name : topic.text +
                "</div></div></div>";
            }

            function formatTopicSelection (topic) {
                // 这里的name是后端返回的  如果后端没有返回name，这里就显示用户输入的text
                return topic.name || topic.text;
            }

            $(".js-example-placeholder-multiple").select2({
                tags: true,
                placeholder: '选择相关话题',
                minimumInputLength: 2,
                ajax: {
                    url:'/api/topics',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function (data, params) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                templateResult: formatTopic,// 返回话题样式
                templateSelection: formatTopicSelection,// 返回选中话题样式
                escapeMarkup: function (markup) { return markup; }
            });
        });
    </script>
    @endsection

    @endsection

    * resources/views/questions/show.blade.php
    @extends('layouts.app')

    @section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ $question->title }}
                        @foreach($question->topics as $topic)
                            <a class="topic pull-right" href="/topic/{{ $topic->id }}">{{ $topic->name }}</a>
                        @endforeach
                    </div>

                    <div class="panel-body">
                        {!! $question->body !!}
                    </div>
                    <div class="edit-actions">
                        @if(Auth::check() && Auth::user()->owns($question))
                            <span class="edif"><a href="/questions/{{ $question->id }}/edit">编 辑</a></span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection

#### 问题Feed和删除问题

    * 增加 questions 的 index.blade.php
    @extends('layouts.app')
    @section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                @foreach($questions as $question)
                    <div class="media">
                        <div class="media-left">
                            <a href="">
                                <img width="48" src="{{ $question->user->avatar }}" alt="{{ $question->user->name }}">
                            </a>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading top-margin">
                                <a href="/questions/{{ $question->id }}">
                                    {{ $question->title }}
                                </a>
                            </h4>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endsection

    * 编辑 questions 控制器
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
            $questions = $this->questionRepository->getQuestionsFeed();
            return view('questions.index', compact('questions'));
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
            $question = $this->questionRepository->getQuestion($id);
            // 判断操作是否是本人
            if (Auth::user()->owns($question)) {
                $question->delete();
                return redirect('/');
            }
            abort(403, 'Forbidden');// return back();
        }


    }

    * 在 Question.php 中定义 user 和 scopePublished 方法
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
         * scope 前缀的模型方法
         * 范围查询可以让您轻松的重复利用模型的查询逻辑。要设定范围查询，只要定义有  scope 前缀的模型方法：
         */
        public function scopePublished($query)
        {
            // 返回允许发布的内容
            return $query->where('is_hidden', 'F');
        }

    }

    * 在 Repositories/QuestionRepository.php 中添加 getQuestionsFeed 方法

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
         * 添加问题
         */
        public function addQuestion(array $attributes)
        {
            return Question::create($attributes);
        }

        /**
         * 获取指定问题
         */
        public function getQuestion($id)
        {
            return Question::find($id);   
        }

        /**
         * 获取全部问题
         */
        public function getQuestionsFeed()
        {   // 返回指定范围数据 并关联相应的发布者
            // latest 在 vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php:1163
            // latest 按时间戳排序
            // wiht 预加载
            return Question::published()
                   ->latest('updated_at')->with('user')->get();
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

#### 实现提交问题的 Answer(回答)
    * 创建 Answer model和表
    php artisan make:model Answer -m

    * 编辑 Answer 数据库表
    <?php

    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;

    class CreateAnswersTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('answers', function (Blueprint $table) {
                $table->increments('id');
                // 记录回复者的用户id
                $table->integer('user_id')->index()->unsigned();
                // 回复与问题对应
                $table->integer('question_id')->index()->unsigned();
                // 回复的内容
                $table->text('body');
                // 点赞统计
                $table->integer('votes_count')->default(0);
                // 评论统计
                $table->integer('comments_count')->default(0); 
                // 是否发布   
                $table->string('is_hidden', 8)->default('F');
                // 是否关闭评论
                $table->string('close_comment', 8)->default('F');  
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('answers');
        }
    }

    * 编辑 Answer model
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

    * 编辑 Question model
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
         * scope 前缀的模型方法
         * 范围查询可以让您轻松的重复利用模型的查询逻辑。要设定范围查询，只要定义有  scope 前缀的模型方法：
         */
        public function scopePublished($query)
        {
            // 返回允许发布的内容
            return $query->where('is_hidden', 'F');
        }

    }

    * 编辑 User model
    <?php
    namespace App;

    use Illuminate\Notifications\Notifiable;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Naux\Mail\SendCloudTemplate;
    use Mail;
    use Illuminate\Database\Eloquent\Model;

    class User extends Authenticatable
    {
        use Notifiable;

        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'name', 'email', 'password', 'avatar', 'confirmation_token',
        ];

        /**
         * The attributes that should be hidden for arrays.
         *
         * @var array
         */
        protected $hidden = [
            'password', 'remember_token',
        ];

        /**
         * 定义回复
         */
        public function answers()
        {
            return $this->hasMany('App\Models\Answer');
        }


        /**
         * 判断登录者与问题发布者是否相同
         */
        public function owns(Model $model)
        {
            return $this->id == $model->user_id;
        }


        /**
         * laravel 不支持sendCloud 模板 重写重置密码邮件发送
         */
        public function sendPasswordResetNotification($token)
        {
            $data = ['url'=>url('password/reset', $token)];
            // 选择模板
            $template = new SendCloudTemplate('password_reset', $data);
            // 发送邮件
            Mail::raw($template, function ($message) {
                // 邮件发送者
                $message->from('3434744@qq.com', '幸福号'); 
                // 邮件接收者
                $message->to($this->email);
            });

        }

    }

    * 实现提交问题回复
    // 创建回复控制器
    php artisan make:controller Home\\AnswersController

    // 创建回复请求验证
    php artisan make:request AnswerRequest

    // 创建提交 Answers 路由
    Route::post('questions/{question}/answer', 'Home\AnswersController@store');

    // 编辑 Answer model
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

    // 修改 Question model
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
         * scope 前缀的模型方法
         * 范围查询可以让您轻松的重复利用模型的查询逻辑。要设定范围查询，只要定义有  scope 前缀的模型方法：
         */
        public function scopePublished($query)
        {
            // 返回允许发布的内容
            return $query->where('is_hidden', 'F');
        }

    }

    // 修改 User model
    <?php
    namespace App;

    use Illuminate\Notifications\Notifiable;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Naux\Mail\SendCloudTemplate;
    use Mail;
    use Illuminate\Database\Eloquent\Model;

    class User extends Authenticatable
    {
        use Notifiable;

        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'name', 'email', 'password', 'avatar', 'confirmation_token',
        ];

        /**
         * The attributes that should be hidden for arrays.
         *
         * @var array
         */
        protected $hidden = [
            'password', 'remember_token',
        ];

        /**
         * 定义回复
         */
        public function answers()
        {
            return $this->hasMany('App\Models\Answer');
        }


        /**
         * 判断登录者与问题发布者是否相同
         */
        public function owns(Model $model)
        {
            return $this->id == $model->user_id;
        }


        /**
         * laravel 不支持sendCloud 模板 重写重置密码邮件发送
         */
        public function sendPasswordResetNotification($token)
        {
            $data = ['url'=>url('password/reset', $token)];
            // 选择模板
            $template = new SendCloudTemplate('password_reset', $data);
            // 发送邮件
            Mail::raw($template, function ($message) {
                // 邮件发送者
                $message->from('3434744@qq.com', '幸福号'); 
                // 邮件接收者
                $message->to($this->email);
            });

        }

    }

    // 修改 show 视图
    @extends('layouts.app')

    @section('content')
    @include('vendor.ueditor.assets')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ $question->title }}
                        @foreach($question->topics as $topic)
                            <a class="topic pull-right" href="/topic/{{ $topic->id }}">{{ $topic->name }}</a>
                        @endforeach
                    </div>

                    <div class="panel-body content">
                        {!! $question->body !!}
                    </div>
                    <div class="edit-actions">
                        @if(Auth::check() && Auth::user()->owns($question))
                            <span class="edif"><a href="/questions/{{ $question->id }}/edit">编 辑</a></span>
                            <form action="/questions/{{$question->id}}" method="post" class="delete-form">
                                {{ method_field('DELETE') }}
                                {{ csrf_field() }}
                                <button class="button is-naked delete-button">删 除</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ $question->answers_count }}个回复
                    </div>

                    <div class="panel-body">
                        @foreach($question->answers as $answer)
                            <div class="media">
                                <div class="media-left">
                                    <a href="">
                                        <img class="top-margin" width="36" src="{{ $answer->user->avatar }}" alt="{{ $answer->user->name }}">
                                    </a>
                                </div>
                                <div class="media-body">
                                    <h4 class="media-heading top-margin">
                                        <a href="/user/{{ $answer->user->name }}">
                                            {{ $answer->user->name }}
                                        </a>
                                    </h4>
                                    {!! $answer->body !!}
                                </div>
                            </div>
                        @endforeach
                        <form action="/questions/{{$question->id}}/answer" method="post">
                            {!! csrf_field() !!}
                            <div class="form-group{{ $errors->has('body') ? 'has-error' : '' }}">
                                <!-- 编辑器容器 -->
                                <!-- 非转义可能引起攻击,需要过滤 -->
                                <script id="container" name="body" type="text/plain" style="height:120px;">
                                    {!! old('body') !!}
                                </script>
                                @if ($errors->has('body'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('body') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <button class="btn btn-success pull-right" type="submit">提交回复</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @section('js')
    <!-- 实例化编辑器 -->
    <script type="text/javascript">
        var ue = UE.getEditor('container', {
            toolbars: [
                    ['bold', 'italic', 'underline', 'strikethrough', 'blockquote', 'insertunorderedlist', 'insertorderedlist', 'justifyleft','justifycenter', 'justifyright',  'link', 'insertimage', 'fullscreen']
                ],
            elementPathEnabled: false,
            enableContextMenu: false,
            autoClearEmptyNode:true,
            wordCount:false,
            imagePopup:false,
            autotypeset:{ indent: true,imageBlockLine: 'center' }
        });
        ue.ready(function() {
            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}'); // 设置 CSRF token.
        });
    </script>
    @endsection

    @endsection


#### 实现用户关注问题
    * 创建用户-问题关系表
    php artisan make:migration create_user_question_table --create=user_question

    * 编辑 user_question 表
    <?php
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;

    class CreateUserQuestionTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('user_question', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned()->index();
                $table->integer('question_id')->unsigned()->index();
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('user_question');
        }
    }

    * 执行命令创建表
    php artisan migrate

    * 执行命令创建 Follow model
    php artisan make:model Models\\Follow

    * 编辑 Follow model
    <?php
    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class Follow extends Model
    {
        protected $table = 'user_question';

        protected $fillable = ['user_id', 'question_id'];
    }

    * 修改 User model
    <?php
    namespace App;

    use Illuminate\Notifications\Notifiable;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Naux\Mail\SendCloudTemplate;
    use App\Models\Follow;
    use Mail;
    use Illuminate\Database\Eloquent\Model;

    class User extends Authenticatable
    {
        use Notifiable;

        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'name', 'email', 'password', 'avatar', 'confirmation_token',
        ];

        /**
         * The attributes that should be hidden for arrays.
         *
         * @var array
         */
        protected $hidden = [
            'password', 'remember_token',
        ];

        /**
         * 定义回复
         */
        public function answers()
        {
            return $this->hasMany('App\Models\Answer');
        }


        /**
         * 判断登录者与问题发布者是否相同
         */
        public function owns(Model $model)
        {
            return $this->id == $model->user_id;
        }

        /**
         * 定义关注关联方法
         */
        public function follows($question)
        {
            return Follow::create([
                'question_id' => $question,
                'user_id' => $this->id
            ]);
        }


        /**
         * laravel 不支持sendCloud 模板 重写重置密码邮件发送
         */
        public function sendPasswordResetNotification($token)
        {
            $data = ['url'=>url('password/reset', $token)];
            // 选择模板
            $template = new SendCloudTemplate('password_reset', $data);
            // 发送邮件
            Mail::raw($template, function ($message) {
                // 邮件发送者
                $message->from('3434744@qq.com', '幸福号'); 
                // 邮件接收者
                $message->to($this->email);
            });

        }

    }

    * 添加路由
    Route::get('question/{question}/follow', 'FollowController@follow');

    * 创建 FollowController 控制器
    php artisan make:controller Home\\FollowController

    * 编辑 FollowController 控制器
    <?php
    namespace App\Http\Controllers\Home;

    use Illuminate\Http\Request;
    use App\Http\Controllers\Controller;
    use Auth;

    class FollowController extends Controller
    {
        public function __construct()
        {
            // 登录限制
            $this->middleware('auth');
        }

        public function follow($question)
        {
            Auth::user()->follows($question);

            return back();
        }
    }

#### 使用vue 实现关注按钮组件化
    * resources/assets/js/components 编辑组件 QuestionFollowButton.vue
    <template>
        <button 
            class="btn btn-default"
            v-bind:class="{ 'btn-success': followed }"
            v-on:click="follow"
            v-text="text"
        ></button>
    </template>

    <script>
        export default {
            props:['question', 'user'],
            mounted() {
                this.$http.post('/api/question/follower',{'question':this.question, 'user':this.user})
                .then(response=>{
                    this.followed = response.data.followed
                })
            },
            data() {
                return {
                    followed: false
                }
            },
            computed: {
                text() {
                    return this.followed ? '已关注':'关注该问题'
                }
            },
            methods: {
                follow() {
                    this.$http.post('/api/question/follow', {'question': this.question, 'user': this.user})
                    .then(response=>{
                        this.followed = response.data.followed
                    })
                }
            }
        }
    </script>

    * 定义 api 路由 
    // 关注组件 勾子调用
    Route::post('/question/follower', function (Request $request) {
        $followed = \App\Models\Follow::where('question_id', $request->get('question'))
            ->where('user_id', $request->get('user'))
            ->count();

        if($followed) {
            return response()->json(['followed' => true]);
        }
        return response()->json(['followed' => false]);
        
    })->middleware('api');

    // 关注 点击响应
    Route::post('/question/follow', function (Request $request) {
        $followed = \App\Models\Follow::where('question_id', $request->get('question'))
            ->where('user_id', $request->get('user'))
            ->first();
            
        // 对查询结果进行判断  非空 删除记录 改变状态
        if($followed !== null) {
            $followed->delete();
            return response()->json(['followed' => false]);
        }
        // 否则 创建记录
        \App\Models\Follow::create([
            'question_id' => $request->get('question'),
            'user_id' => $request->get('user'),
        ]);
        // 返回状态
        return response()->json(['followed' => true]);

    })->middleware('api');


#### 前后端分离 Api token 认证
    * laravel 默认使用 api_token 字段
    * 创建添加 api_token 表 用于向 User 添加 api_token
    php artisan make:migration add_api_token_to_users --table=users
    *编辑 AddApiTokenToUsers 表
    <?php
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;

    class AddApiTokenToUsers extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::table('users', function (Blueprint $table) {
                $table->string('api_token', 64)->unique();
            });
        }

        /**
         * Reverse the migrations.
         * 删除
         * @return void
         */
        public function down()
        {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['api_token']);
            });
        }
    }

    * 执行 php artisan migrate 添加 api_token 字段
    * 手工创建测试用 api_token 
    首先执行 php artisan tinker 进入命令行
    创建60位字符串 str_random(60)
    然后将生成的字符串手工添加进 api_token 字段

    * 修改注册用户表，添加api_token字段
    protected function create(array $data)
    {
        // 判断邮箱是否存在

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            // 用户默认用户头像
            'avatar' => '/images/avatart/user.png', // 头像目录
            // 邮箱验证 token
            'confirmation_token' => str_random(50), // 使用laravel 提供的函数生成50位的字串
            'password' => bcrypt($data['password']),// 密码加密
            'api_token' => str_random(60),// 添加用户认证api_token
        ]);

        // 邮件发送方法一 自定义发送邮件(不需要配置env文件)
        // $this->emailSendToVerify($user);
        // 邮件发送方法二 调用sendcloud 模板
        $this->sendVerifyEmailTo($user);
        return $user;
    }

    * 修改User model 添加 api_token 字段
    * 进入 resources/assets/js/bootstrap.js 文件,添加自定义token
    Vue.http.interceptors.push((request, next) => {
        request.headers.set('X-CSRF-TOKEN', Laravel.csrfToken);
        request.headers.set('Authorization', Laravel.apiToken);

        next();
    });
    * 进入 app.blade.php 文件添加 apiToken
    <script>
        window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>;
        Laravel.apiToken = "{{ Auth::check() ? 'Bearer '.Auth::user()->api_token : 'Bearer ' }}";
    </script>

    * 修改 api 路由
    <?php
    use Illuminate\Http\Request;

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:api');

    Route::get('/topics', function (Request $request) {
        $topics = \App\Models\topic::select(['id', 'name'])
                ->where('name', 'like', '%'.$request->query('q').'%')
                ->get();
        return $topics;
    })->middleware('api');

    // 关注组件 勾子调用
    Route::post('/question/follower', function (Request $request) {
        $user = Auth::guard('api')->user();
        $followed = $user->followed($request->get('question'));

        if($followed) {
            return response()->json(['followed' => true]);
        }
        return response()->json(['followed' => false]);
        
    })->middleware('auth:api');

    // 关注 点击响应
    Route::post('/question/follow', function (Request $request) {
        $user = Auth::guard('api')->user();
        $question = \App\Models\Question::find($request->get('question'));
        $followed = $user->followThis($question->id);
        
        // $followed 返回两数组结果集 detached attached
        // 对查询结果进行判断  非空 删除记录 改变状态
        if(count($followed['detached'] > 0)) {
            $question->decrement('followers_count');
            return response()->json(['followed' => false]);
        }
        // 否则 创建记录
        $question->increment('followers_count');
        // 返回状态
        return response()->json(['followed' => true]);

    })->middleware('auth:api');

    * 修改 show 视图
    @extends('layouts.app')

    @section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ $question->title }}
                        @foreach($question->topics as $topic)
                            <a class="topic pull-right" href="/topic/{{ $topic->id }}">{{ $topic->name }}</a>
                        @endforeach
                    </div>

                    <div class="panel-body content">
                        {!! $question->body !!}
                    </div>
                    <div class="edit-actions">
                        @if(Auth::check() && Auth::user()->owns($question))
                            <span class="edif"><a href="/questions/{{ $question->id }}/edit">编 辑</a></span>
                            <form action="/questions/{{$question->id}}" method="post" class="delete-form">
                                {{ method_field('DELETE') }}
                                {{ csrf_field() }}
                                <button class="button is-naked delete-button">删 除</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3 pull-right">
                <div class="panel panel-default">
                    <div class="panel-heading question-follow">
                        <h2>{{ $question->followers_count }}</h2>
                        <span>关注者</span>
                    </div>
                    <div class="panel-body">
                    @if(Auth::check())
                        <question-follow-button question="{{$question->id}}"></question-follow-button>
                        <a href="#editor" class="btn btn-primary">撰写答案</a>
                    @else
                        <a href="{{url('login')}}" class="btn btn-default">关注该问题</a>
                        <a href="{{url('login')}}" class="btn btn-primary">撰写答案</a>
                    @endif
                    </div>
                </div>
            </div>
            <div class="col-md-8 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ $question->answers_count }}个回复
                    </div>

                    <div class="panel-body">
                        @foreach($question->answers as $answer)
                            <div class="media">
                                <div class="media-left">
                                    <a href="">
                                        <img class="top-margin" width="36" src="{{ $answer->user->avatar }}" alt="{{ $answer->user->name }}">
                                    </a>
                                </div>
                                <div class="media-body">
                                    <h4 class="media-heading top-margin">
                                        <a href="/user/{{ $answer->user->name }}">
                                            {{ $answer->user->name }}
                                        </a>
                                    </h4>
                                    {!! $answer->body !!}
                                </div>
                            </div>
                        @endforeach
                        @if(Auth::check())
                        <form action="/questions/{{$question->id}}/answer" method="post">
                            {!! csrf_field() !!}
                            <div class="form-group{{ $errors->has('body') ? 'has-error' : '' }}">
                                <!-- 编辑器容器 -->
                                <!-- 非转义可能引起攻击,需要过滤 -->
                                <script id="container" name="body" type="text/plain" style="height:120px;">
                                    {!! old('body') !!}
                                </script>
                                @if ($errors->has('body'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('body') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <button class="btn btn-success pull-right" type="submit">提交回复</button>
                        </form>
                        @else
                        <a href="{{ url('login') }}" class="btn btn-success btn-block">登录提交答案</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @section('js')
    <!-- 实例化编辑器 -->
    <script type="text/javascript">
        var ue = UE.getEditor('container', {
            toolbars: [
                    ['bold', 'italic', 'underline', 'strikethrough', 'blockquote', 'insertunorderedlist', 'insertorderedlist', 'justifyleft','justifycenter', 'justifyright',  'link', 'insertimage', 'fullscreen']
                ],
            elementPathEnabled: false,
            enableContextMenu: false,
            autoClearEmptyNode:true,
            wordCount:false,
            imagePopup:false,
            autotypeset:{ indent: true,imageBlockLine: 'center' }
        });
        ue.ready(function() {
            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}'); // 设置 CSRF token.
        });
    </script>
    @endsection

    @endsection

#### 关注用户 
    * 创建用户与用户关注关系表
    php artisan make:migration create_followers_table --create=followers

    * 编辑 followers 表
    <?php
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;

    class CreateFollowersTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('followers', function (Blueprint $table) {
                $table->increments('id');
                // 关注用户
                $table->integer('follower_id')->unsigned()->index();
                // 被关注用户
                $table->integer('followed_id')->unsigned()->index();
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('followers');
        }
    }

    * 创建 followers 关系表
    php artisan migrate

    * User model中添加 followers 方法
    <?php
    namespace App;

    use Illuminate\Notifications\Notifiable;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Naux\Mail\SendCloudTemplate;
    use Mail;
    use Illuminate\Database\Eloquent\Model;
    use App\Models\Follow;

    class User extends Authenticatable
    {
        use Notifiable;

        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'name', 'email', 'password', 'avatar', 'confirmation_token', 'api_token'
        ];

        /**
         * The attributes that should be hidden for arrays.
         *
         * @var array
         */
        protected $hidden = [
            'password', 'remember_token',
        ];

        /**
         * 定义回复
         */
        public function answers()
        {
            return $this->hasMany('App\Models\Answer');
        }


        /**
         * 判断登录者与问题发布者是否相同
         */
        public function owns(Model $model)
        {
            return $this->id == $model->user_id;
        }

        /**
         * 定义多对多关系
         * 定义用户-问题 多对多关系
         */
        public function follows()
        {
            return $this->belongsToMany('App\Models\Question', 'user_question')->withTimestamps();
        }

        /**
         * 关注操作
         */
        public function followThis($question)
        {
            // toggle 方法实现关系存在  删除，否则反之
            // toggle 一般用在多对多
            return $this->follows()->toggle($question);
        }

        /**
         * 关注样式选择
         */
        public function followed($question)
        {
            // !! 强制取反，返回bool值
            return $this->follows()->where('question_id', $question)->count();
        }

        /**
         * 用户 关注 用户
         */
        public function followers()
        {
            // 因为是用户关注用户 self::class(自己调用自己)
            return $this->belongsToMany(self::class, 'followers', 'follower_id', 'followed_id')->withTimestamps();
        }

        /**
         * laravel 不支持sendCloud 模板 重写重置密码邮件发送
         */
        public function sendPasswordResetNotification($token)
        {
            $data = ['url'=>url('password/reset', $token)];
            // 选择模板
            $template = new SendCloudTemplate('password_reset', $data);
            // 发送邮件
            Mail::raw($template, function ($message) {
                // 邮件发送者
                $message->from('3434744@qq.com', '幸福号'); 
                // 邮件接收者
                $message->to($this->email);
            });

        }

    }


    * 修改 show 视图文件
    @extends('layouts.app')

    @section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ $question->title }}
                        @foreach($question->topics as $topic)
                            <a class="topic pull-right" href="/topic/{{ $topic->id }}">{{ $topic->name }}</a>
                        @endforeach
                    </div>

                    <div class="panel-body content">
                        {!! $question->body !!}
                    </div>
                    <div class="edit-actions">
                        @if(Auth::check() && Auth::user()->owns($question))
                            <span class="edif"><a href="/questions/{{ $question->id }}/edit">编 辑</a></span>
                            <form action="/questions/{{$question->id}}" method="post" class="delete-form">
                                {{ method_field('DELETE') }}
                                {{ csrf_field() }}
                                <button class="button is-naked delete-button">删 除</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3 pull-right">
                <div class="panel panel-default">
                    <div class="panel-heading question-follow">
                        <h2>{{ $question->followers_count }}</h2>
                        <span>关注者</span>
                    </div>
                    <div class="panel-body">
                    @if(Auth::check())
                        <question-follow-button question="{{$question->id}}"></question-follow-button>
                        <a href="#editor" class="btn btn-primary pull-right">撰写答案</a>
                    @else
                        <a href="{{url('login')}}" class="btn btn-default">关注该问题</a>
                        <a href="{{url('login')}}" class="btn btn-primary pull-right">撰写答案</a>
                    @endif
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading question-follow">
                        <h2>关于作者</h2>
                    </div>
                    <div class="panel-body">
                        <div class="panel-body">
                            <div class="media-left">
                                <a href="#">
                                    <img width="36" src="{{$question->user->avatar}}" alt="{{$question->user->name}}">
                                </a>
                            </div>
                            <div class="media-body">
                                <h4 class="media-heading">
                                    <a href="">{{ $question->user->name }}</a>
                                </h4>
                            </div>
                            <div class="user-statics">
                                <div class="statics-item text-center">
                                    <div class="statics-text">问题</div>
                                    <div class="statics-count">{{ $question->user->questions_count }}</div>
                                </div>
                                <div class="statics-item text-center">
                                    <div class="statics-text">回答</div>
                                    <div class="statics-count">{{ $question->user->questions_count }}</div>
                                </div>
                                <div class="statics-item text-center">
                                    <div class="statics-text">关注</div>
                                    <div class="statics-count">{{ $question->user->questions_count }}</div>
                                </div>
                            </div>
                        </div>
                        <question-follow-button question="{{$question->id}}"></question-follow-button>
                        <a href="#editor" class="btn btn-default pull-right">发送私信</a>
                    </div>
                </div>
            </div>

            <div class="col-md-8 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ $question->answers_count }}个回复
                    </div>

                    <div class="panel-body">
                        @foreach($question->answers as $answer)
                            <div class="media">
                                <div class="media-left">
                                    <a href="">
                                        <img class="top-margin" width="36" src="{{ $answer->user->avatar }}" alt="{{ $answer->user->name }}">
                                    </a>
                                </div>
                                <div class="media-body">
                                    <h4 class="media-heading top-margin">
                                        <a href="/user/{{ $answer->user->name }}">
                                            {{ $answer->user->name }}
                                        </a>
                                    </h4>
                                    {!! $answer->body !!}
                                </div>
                            </div>
                        @endforeach
                        @if(Auth::check())
                        <form action="/questions/{{$question->id}}/answer" method="post">
                            {!! csrf_field() !!}
                            <div class="form-group{{ $errors->has('body') ? 'has-error' : '' }}">
                                <!-- 编辑器容器 -->
                                <!-- 非转义可能引起攻击,需要过滤 -->
                                <script id="container" name="body" type="text/plain" style="height:120px;">
                                    {!! old('body') !!}
                                </script>
                                @if ($errors->has('body'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('body') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <button class="btn btn-success pull-right" type="submit">提交回复</button>
                        </form>
                        @else
                        <a href="{{ url('login') }}" class="btn btn-success btn-block">登录提交答案</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @section('js')
    <!-- 实例化编辑器 -->
    <script type="text/javascript">
        var ue = UE.getEditor('container', {
            toolbars: [
                    ['bold', 'italic', 'underline', 'strikethrough', 'blockquote', 'insertunorderedlist', 'insertorderedlist', 'justifyleft','justifycenter', 'justifyright',  'link', 'insertimage', 'fullscreen']
                ],
            elementPathEnabled: false,
            enableContextMenu: false,
            autoClearEmptyNode:true,
            wordCount:false,
            imagePopup:false,
            autotypeset:{ indent: true,imageBlockLine: 'center' }
        });
        ue.ready(function() {
            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}'); // 设置 CSRF token.
        });
    </script>
    @endsection

    @endsection


#### 用户关注组件化
    * 创建组件

    * 添加路由

    * 创建控制器

    * 编辑控制器






