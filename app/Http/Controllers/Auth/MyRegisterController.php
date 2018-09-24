<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\User;
use Illuminate\Support\Facades\Auth;


use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class MyRegisterController extends Controller
{
	use RegistersUsers;

    protected $redirectTo = '/';

    public function showRegistrationForm()
    {
    	
    	return view('auth.register');
    }

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            //'family_name'	=> 'required|string|max:255',
            //'name'			=> 'required|string|max:255',
            //'patronymic'	=> 'required|string|max:255',
            //'photo'			=> 'required|string|max:255',
            'login'			=> 'required|string|min:3|max:50|unique:users',
            //'sex'			=> 'required|string|max:255',
            //'about'			=> 'required|string|max:255',
            'email'			=> 'required|string|email|max:50|unique:users',
            'password'		=> 'required|string|min:6|max:50|confirmed'
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            //'family_name'	=> $data['family_name'],
            //'name'			=> $data['name'],
            //'patronymic'	=> $data['patronymic'],
            //'photo'			=> $data['photo'],
            'login'			=> $data['login'],
            //'sex'			=> $data['sex'],
            //'about'			=> $data['about'],
            'email'			=> $data['email'],
            'password'		=> Hash::make($data['password'])
        ]);
    }

    public function confirm($confirm_token)
    {
        $user=User::where('confirm_token', $confirm_token)->first();

        // Проверка email прошла успешно (токены совпали)

            if (isset($user)) {
                $user->confirm_token=null;
                $user->save();

                $data_arr = [
                    'login' => $user->login,
                ];

                $this->guard()->login($user);

                return view('auth.confirmSuccess', $data_arr);
            }

        // Проверка email провалена (токены не совпали)

            else {
                return view('auth.confirmFail');
            }
    }
}

/*оригинал
namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{

    use RegistersUsers;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'login' => 'required|string|max:255|unique:users,login',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'login' => $data['login'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}*/