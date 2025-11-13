<?php

namespace App\Http\Requests\Task;

use App\Domain\Task\Enums\TaskPriority;
use App\Domain\Task\Enums\TaskStatus;
use App\Rules\NoXss;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', new NoXss()],
            'description' => ['nullable', 'string', 'max:1000', new NoXss()],
            'status' => ['nullable', Rule::enum(TaskStatus::class)],
            'priority' => ['nullable', Rule::enum(TaskPriority::class)],
            'due_date' => ['nullable', 'date', 'after:now'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Название задачи обязательно для заполнения',
            'title.max' => 'Название задачи не должно превышать 255 символов',
            'description.max' => 'Описание не должно превышать 1000 символов',
            'priority.in' => 'Приоритет должен быть одним из: ' . implode(', ', array_column(TaskPriority::cases(), 'value')),
            'due_date.date' => 'Дата выполнения должна быть корректной датой',
            'due_date.after' => 'Дата выполнения должна быть в будущем',
            'assigned_to.exists' => 'Выбранный пользователь не существует',
        ];
    }
}
