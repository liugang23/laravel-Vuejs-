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

    // 添加 select2 到本地实现话题标签
    * 首先下载select2 相关样式文件
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

        // select2 样式添加完成后，开始添加相应方法
        * 添加多选css
        <select class="js-example-basic-multiple" multiple="multiple">
            <option value="AL">Alabama</option>
                ...
            <option value="WY">Wyoming</option>
        </select>

        * 添加多选js
        <script type="text/javascript">
            $(".js-example-basic-multiple").select2();
        </script>

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
    


[http://select2.github.io/](http://select2.github.io/)

    // QuestionsController 控制器添加方法
