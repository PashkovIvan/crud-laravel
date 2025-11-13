<?php

namespace App\Http\Requests\Notification;

use App\Domain\Notification\Enums\NotificationType;
use App\Rules\NoXss;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotificationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', new NoXss()],
            'message' => ['required', 'string', 'max:1000', new NoXss()],
            'type' => ['nullable', Rule::enum(NotificationType::class)],
            'data' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Заголовок уведомления обязателен для заполнения',
            'title.max' => 'Заголовок не должен превышать 255 символов',
            'message.required' => 'Сообщение уведомления обязательно для заполнения',
            'message.max' => 'Сообщение не должно превышать 1000 символов',
            'type.in' => 'Тип уведомления должен быть одним из: ' . implode(', ', array_column(NotificationType::cases(), 'value')),
        ];
    }
}
