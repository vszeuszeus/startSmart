<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use AuthenticatesUsers;

    protected function createPassword(){

        return str_random(8);

    }

    public function sendMessage($phone, $password)
    {
        $http = new Client();
        return $http->request('GET','https://api.mobizon.kz/service/message/sendsmsmessage?'
            .'apiKey=kzad41e4ccb9f6a151d1dbf8835d6e48bd48a3a50b0a93f361819138ba8585dfaf5ba7'
            .'&recipient='.$phone.'&text='.$password)->getBody();
    }

    protected function createUser($data)
    {

        $password = $this->createPassword();

        $user = User::create([
            'phone' => $data->phone,
            'api_token' => str_random('60'),
            'password' => Hash::make($password),
            'name' => $data->input('name', ''),
            'email' => $data->input('email', '')
        ]);

        if(dd($this->sendMessage($data->phone,
            'Ваш пароль от сайта startSmart.Kz - '.$password.'')))
            $user->passwordMessages()->create();

        return ['result' => true, 'details' => 'User created'];

    }

    protected function updateUser($user){

        $password = $this->createPassword();

        $user->password = Hash::make($password);

        if(!$user->save())
            return ['result' => false, 'details' => 'User not updated'];

        $message = 'Ваш пароль от сайта startSmartKz изменен на - '.$password;

        if(!$this->sendMessage($user->phone, $message))
            return ['result' => false, 'details' => 'Password did not sent'];

        $user->passwordMessages()->create();

        return ['result' => true, 'details' => 'User updated. Password sent'];



    }

    protected function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'phone' => 'required|integer|digits:11',
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email'
        ]);

        if($validator->fails())
            return response([
                'errors' => $validator->errors()->all()
            ], 422);

        $user = User::where('phone', '=', $request->phone)
            ->with(['passwordMessages' => function($query){
                $query->where('created_at', '<', now());
            }])
            ->first();

        if(!$user){
            return $this->createUser($request);
        }

        if($user->password_messages){
            return ['result' => false, 'errors' => [
                'time' => [
                    'TimeOut 60 second last message'
                ]
            ]];
        }

        return $this->updateUser($user);

    }

    public function getToken(Request $request)
    {

        $validator = Validator::make($request->all(), [
            $this->username() => 'required|digits:11',
            'password' => 'required|max:255'
        ]);

        if($validator->fails())
            return response([
                'errors' => $validator->errors()->all()
            ], 422);

        if(Auth::once($this->credentials($request))){
            return [
                'result' => true,
                'api_token' => $request->user()->api_token
            ];
        }

        return response([
            'errors' => [
                'auth' => [
                    'Error in phone or password'
                ]
            ]
        ], 422);

    }

    public function username()
    {
        return 'phone';
    }



}
