<?php
namespace App\Mailer;

use App\User;
use Auth;

class SendMailer extends Mailer
{
	/**
     * 用户关注 邮件发送
	 */
	public function followNotifyEmail($email)
	{
		$data = ['url'=>'http://www.zt.com', 'name'=>Auth::guard('api')->user()->name];

		$this->sendTo('app_new_user_follow', $email, $data);
	}

	/**
	 * 密码重置 邮件发送
	 */
	public function passwordReset($email, $token)
	{
		$data = ['url'=>url('password/reset', $token)];

		$this->sendTo('password_reset', $email, $data);
	}

	/**
	 * 注册 邮件发送
	 */
	public function welcome(User $user)
	{
		$data = [
            'url'=>route('email.verify',['token'=>$user->confirmation_token]),
            'name'=>$user->name
        ];

        $this->sendTo('test_template', $user->email, $data);
	}

}