<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'f_name' => $this->f_name,
            'l_name' => $this->l_name,
            'email'  => $this->email,
            'role'   => $this->role?->name,
            'permissions' => $this->permissions(),
        ];
    }
}
