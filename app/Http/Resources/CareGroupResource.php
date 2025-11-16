<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CareGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->care_group_id,
            'name' => $this->name,
            'photo' => $this->photo_url,
            'admin' => new UserResource($this->whenLoaded('admin')),
            'patient' => new PatientResource($this->whenLoaded('patient')),
            'members' => UserResource::collection($this->whenLoaded('members')),
        ];
    }
}
