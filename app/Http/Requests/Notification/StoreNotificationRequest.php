<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:1000'],
            'type' => ['nullable', 'string', 'in:info,warning,error,success'],
            'data' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Заголовок уведомления обязателен для заполнения.',
            'title.max' => 'Заголовок не должен превышать 255 символов.',
            'message.required' => 'Сообщение уведомления обязательно для заполнения.',
            'message.max' => 'Сообщение не должно превышать 1000 символов.',
            'type.in' => 'Тип уведомления должен быть одним из: info, warning, error, success.',
        ];
    }
}
