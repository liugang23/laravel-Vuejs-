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


#### 创建控制器

    php artisan make:controller Home\\QuestionsController --resource
    // 查看路由
    php artisan route:list

    // 编辑控制器
    <?php

    namespace App\Http\Controllers\Home;

    use Illuminate\Http\Request;
    use App\Http\Controllers\Controller;
    use App\models\Question;
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

    // 编辑路由
    Route::resource('questions', 'Home\QuestionsController', ['names'=>[
        'create' => 'question.create',
        'show' => 'question.show',
    ]]);

