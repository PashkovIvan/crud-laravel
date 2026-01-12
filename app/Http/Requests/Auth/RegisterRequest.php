<?php

namespace App\Http\Requests\Auth;

use App\Rules\NoXss;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', new NoXss()],
            // problem: а тут от xss не надо защиту?
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            // problem: а тут от xss не надо защиту?
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Имя обязательно для заполнения',
            'email.required' => 'Email обязателен для заполнения',
            'email.email' => 'Email должен быть корректным адресом электронной почты',
            'email.unique' => 'Пользователь с таким email уже существует',
            'password.required' => 'Пароль обязателен для заполнения',
            'password.min' => 'Пароль должен содержать минимум 8 символов',
            'password.confirmed' => 'Подтверждение пароля не совпадает',
        ];
    }
}
