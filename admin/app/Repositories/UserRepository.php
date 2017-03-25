<?php
namespace App\Repositories;

use App\User;

class UserRepository
{
	public function getUserId($id)
	{
		return User::find($id);
	}
}