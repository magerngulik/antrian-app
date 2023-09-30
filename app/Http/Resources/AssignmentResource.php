<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "user_id" => $this->user_id,
            "role_users_id" => $this->role_users_id,
            "nama_role" => $this->role->nama_role,
            "name" => $this->user->name,
            "kode" => $this->queue->kode,
            "status" => $this->queue->status,
    ];
    }
}
