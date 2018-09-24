<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use Auth;
use App\User;

class MyLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function validator(array $data)
    {
        // $errors = array(
        //     'login.max'             => 'логин (или email) слишком длинный',
        //     'login.min'             => 'логин слишком короткий',

        //     'password.max'          => 'пароль слишком длинный',
        //     'password.min'          => 'пароль слишком короткий',
        // );

        return Validator::make($data, [
            'login'         => 'required|string|min:3|max:50',
            'password'      => 'required|string|min:6|max:50'
        ]);//, $errors
    }

    public function checkLogin(string $login, array &$errors)
    {
        $user;

        if(filter_var($login, FILTER_VALIDATE_EMAIL))
            $user = User::where('email', $login)->get();
        else 
            $user = User::where('login', $login)->get();

        if(isset($user[0])) return $user[0];
        else {
            $errors['login'] = 'Логин или email указан не верно';
            return false;
        }

    }

    public function checkPassword(User $user, string $password, array &$errors)
    {
        if(Hash::check($password, $user->password))
            return true;
        else {
            $errors['password'] = 'Пароль указан не верно';
            return false;
        }
    }

    public function login(Request $request)
    {
        $array = $request->all();
        $remember = $request->has('remember');
        $fieldsForSave = $request->only('login', 'remember');

        $errors = array();

        // Валидация входящих данных

            $this->validator($array)->validate();

        // Проверка логина / email'a пользователя

            $user = $this->checkLogin($array['login'], $errors);

            if(!$user)
                return redirect()->back()->withInput($fieldsForSave)->withErrors($errors);

        // Проверка пароля

            if(!$this->checkPassword($user, $array['password'], $errors))
                return redirect()->back()->withInput($fieldsForSave)->withErrors($errors);

        // Проверка подтвердил ли пользователь email

            if($user->confirm_token !== null) {
                $data_arr = [
                    'login' => $user->login,
                    'email' => $user->email,
                ];

                return view('auth.checkEmailForLogin', $data_arr);
            }

        // Вход

            Auth::login($user, $remember);
            return redirect()->intended('/');
    }
    
}