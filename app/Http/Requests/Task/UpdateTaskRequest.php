<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'priority' => ['nullable', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
            'status' => ['sometimes', 'in:pending,in_progress,completed'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Название задачи не должно превышать 255 символов.',
            'description.max' => 'Описание не должно превышать 1000 символов.',
            'priority.in' => 'Приоритет должен быть одним из: low, medium, high.',
            'due_date.date' => 'Дата выполнения должна быть корректной датой.',
            'status.in' => 'Статус должен быть одним из: pending, in_progress, completed.',
            'assigned_to.exists' => 'Выбранный пользователь не существует.',
        ];
    }
}
