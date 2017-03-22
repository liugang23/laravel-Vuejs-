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
