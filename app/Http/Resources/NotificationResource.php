<?php

namespace App\Http\Resources;

use App\Domain\Common\Helpers\IdHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => IdHelper::encrypt($this->id),
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'read_at' => $this->read_at?->format('c'),
            'data' => $this->when($this->data !== null, fn() => $this->data),
            'created_at' => $this->created_at->format('c'),
            'updated_at' => $this->updated_at->format('c'),
        ];
    }
}
