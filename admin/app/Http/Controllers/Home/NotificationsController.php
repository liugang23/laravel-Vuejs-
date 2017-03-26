<?php
namespace App\Http\Controllers\Home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;

class NotificationsController extends Controller
{
    public function index()
    {
    	$user = Auth::user();
    	return view('notifications.index', compact('user'));
    }
}
