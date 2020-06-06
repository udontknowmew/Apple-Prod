<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\User;
use \App\Setting;
use \App\Billing;
use \App\Card;

class AdminController extends Controller
{
  public $setting;
  public function __construct(Request $r) {
    $token    = ($r->header('token')) ? $r->header('token') : false;
    if ($token) {
      $this->setting  = Setting::where(['token' =>  $token])->first();
    }
  }

  public function index() {
    return view('admin');
  }

  public function fetch() {
    $response   = [
      'login'   =>  User::all(),
      'billing' =>  count(Billing::all()),
      'card'    =>  count(Card::all())
    ];
    return $response;
  }

  public function login(Request $r) {
    $this->validate($r, [
      'username'  =>  'required',
      'password'  =>  'required'
    ]);

    $admin      = Setting::where($r->only('username', 'password'))->first();
    if ($admin['token']) {
      return response()->json([
        'token' =>  $admin['token']
      ], 200);
    }
    return response()->json([], 422);
  }
}
