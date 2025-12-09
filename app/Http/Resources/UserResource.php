<?php

namespace App\Http\Resources;

use App\Domain\Common\Helpers\IdHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => IdHelper::encrypt($this->id),
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->whenLoaded('roles', fn() => $this->roles->pluck('name')),
            'created_at' => $this->created_at->format('c'),
            'updated_at' => $this->updated_at->format('c'),
        ];
    }
}
