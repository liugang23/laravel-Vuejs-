<?php
namespace App\Mailer;

use Naux\Mail\SendCloudTemplate;
use Mail;


class Mailer
{
	protected function sendTo($template, $email, array $data)
	{
		$content = new SendCloudTemplate($template, $data);

		Mail::raw($content, function ($message) use ($email) {
			$message->from('3434744@qq.com', '幸福号');
			$message->to($email);
		});
	}
}