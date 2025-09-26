<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'priority' => ['nullable', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date', 'after:now'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Название задачи обязательно для заполнения.',
            'title.max' => 'Название задачи не должно превышать 255 символов.',
            'description.max' => 'Описание не должно превышать 1000 символов.',
            'priority.in' => 'Приоритет должен быть одним из: low, medium, high.',
            'due_date.date' => 'Дата выполнения должна быть корректной датой.',
            'due_date.after' => 'Дата выполнения должна быть в будущем.',
            'assigned_to.exists' => 'Выбранный пользователь не существует.',
        ];
    }
}
