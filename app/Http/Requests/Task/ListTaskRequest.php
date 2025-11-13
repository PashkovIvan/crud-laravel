<?php

namespace App\Http\Requests\Task;

use App\Domain\Common\Constants\PaginationConstants;
use Illuminate\Foundation\Http\FormRequest;

class ListTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:' . PaginationConstants::MIN_PER_PAGE, 'max:' . PaginationConstants::MAX_PER_PAGE],
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.integer' => 'Количество элементов на странице должно быть числом',
            'per_page.min' => 'Количество элементов на странице должно быть не менее ' . PaginationConstants::MIN_PER_PAGE,
            'per_page.max' => 'Количество элементов на странице не должно превышать ' . PaginationConstants::MAX_PER_PAGE,
        ];
    }
}
