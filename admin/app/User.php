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
     * 定义用户-问题 多对多关系
     */
    public function follows()
    {
        return $this->belongsToMany('App\Models\Question', 'user_question')->withTimestamps();
    }

    /**
     * 执行关注操作
     */
    public function followThis($question)
    {
        // toggle 判断关系是否存在，不存在 建立，存在 删除
        return $this->follows()->toggle($question);
    }

    /**
     * 关注样式 选择
     */
    public function followed($question)
    {
        // !! 强制取反，返回Booleans 值
        return !! $this->follows()->where('question_id', $question)->count();
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
