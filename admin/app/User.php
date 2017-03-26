<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use App\Mailer\SendMailer;
use App\Models\Follow;
use Mail;

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
     * 关注 被关注用户
     */
    public function followers()
    {
        // 因为是用户关注用户 self::class(自己调用自己)
        return $this->belongsToMany(self::class, 'followers', 'follower_id', 'followed_id')->withTimestamps();
    }

    /**
     * 被关注用户 关注
     */
    public function followersUser()
    {
        // 因为是用户关注用户 self::class(自己调用自己)
        return $this->belongsToMany(self::class, 'followers', 'followed_id', 'follower_id')->withTimestamps();
    }

    /**
     * 关注\取消关注 用户
     */
    public function followThisUser($user)
    {
        // 调用多对多查询 toggle对查询结果 取反操作
        return $this->followers()->toggle($user);
    }


    /**
     * laravel 不支持sendCloud 模板 重写重置密码邮件发送
     */
    public function sendPasswordResetNotification($token)
    {
        (new SendMailer())->passwordReset($this->email, $token);
    }

}
