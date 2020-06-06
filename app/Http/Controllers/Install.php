<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Setting;

class Install extends Controller
{
  public function init(Request $req) {
    if (!filter_var($req->input('settings')['email'], FILTER_VALIDATE_EMAIL)) {
      return response()->json([ 'Invalid email' ], 401);
    }
    $admin    = $req->input('admin');
    if (empty($admin['username']) || empty($admin['password']))
    {
      return response()->json([ 'ERROR' ], 401);
    }
    $smtp     = $req->input('smtp');
    $isSmtp   = ($smtp) ? 1 : 0;

    if (!$isSmtp) {
      Setting::create([
        'username'      =>  $admin['username'],
        'password'      =>  $admin['password'],
        'token'         =>  md5(json_encode($admin)),
        'is_smtp'       =>  $isSmtp,
        'email_result'  =>  $req->input('settings')['email']
      ]);
    } else {
      Setting::create([
        'username'      =>  $admin['username'],
        'password'      =>  $admin['password'],
        'token'         =>  md5(json_encode($admin)),
        'is_smtp'       =>  $isSmtp,
        'email_result'  =>  $req->input('settings')['email'],
        'smtp_server'   =>  $smtp['server'],
        'smtp_port'     =>  $smtp['port'],
        'smtp_user'     =>  $smtp['username'],
        'smtp_password' =>  $smtp['password']
      ]);
    }
    return response()->json([], 200);
  }
}
