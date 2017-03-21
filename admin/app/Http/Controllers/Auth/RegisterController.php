<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
//use App\Tools\EMAILResult;
use Naux\Mail\SendCloudTemplate;
use Mail;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

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

        // 邮件发送方法一 自定义发送邮件(不需要配置env文件)
        // $this->emailSendToVerify($user);
        // 邮件发送方法二 调用sendcloud 模板
        $this->sendVerifyEmailTo($user);
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
