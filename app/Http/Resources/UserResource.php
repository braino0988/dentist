<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'type'=>$this->is_employee ? 'employee' : 'customer',
            'email'=>$this->email,
            'phone'=>$this->phone ?? '0000',
            'address'=>$this->address ?? null,
            'roles'=>$this->is_employee ? $this->roles()->pluck('type') : 'customer only',
        ];
    }
}
