<?php

namespace App\Http\Requests\Task;

use App\Domain\User\Models\User;
use App\Rules\ExistsEncryptedId;
use Illuminate\Foundation\Http\FormRequest;

class AssignTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'string', new ExistsEncryptedId(User::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'ID пользователя обязателен для заполнения',
            'user_id.exists' => 'Выбранный пользователь не существует',
        ];
    }
}
