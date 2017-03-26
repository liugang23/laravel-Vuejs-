<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Tools\EMAILResult;
use App\Channels\SendcloudChannel;
use App\Mailer\SendMailer;
use Auth;
use Mail;

class NewUserFollowNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // return ['mail'];// 默认 email
        return ['database', SendcloudChannel::class];// 这里使用数据库
    }

    /**
     * 记录到数据库
     */
    public function toDatabase($notifiable)
    {
        return [
            // 发起关注请求 用户的名字
            'name' => Auth::guard('api')->user()->name,
        ];
    }

    /**
     * 发送邮件
     */
    public function toSendcloud($notifiable)
    {
        /* 自定义模板发送邮件 */
        // 邮件内容
        // $EMAILResult = new EMAILResult();
        // $EMAILResult->to = Auth::guard('api')->user()->email;
        // $EMAILResult->cc = '3434744@qq.com';
        // $EMAILResult->subject = '用户关注提示';
        // $EMAILResult->content = '你好,知乎app上 '.Auth::guard('api')->user()->name.' 关注了你';
        // 发送邮件
        // Mail::send('email_follow', ['EMAILResult' => $EMAILResult], function($m) use ($EMAILResult) {
        //     $m->to($EMAILResult->to, '尊敬的用户')
        //     ->cc($EMAILResult->cc)
        //     ->subject($EMAILResult->subject);
        // });

        /* 使用Sendcloud 的模板发送邮件 */
        (new SendMailer())->followNotifyEmail($notifiable->email);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // return (new MailMessage)
        //             ->line('The introduction to the notification.')
        //             ->action('Notification Action', 'https://laravel.com')
        //             ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
