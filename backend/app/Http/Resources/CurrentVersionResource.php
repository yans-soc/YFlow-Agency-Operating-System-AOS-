<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrentVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'version' => $this->version,
            'formatted_version' => $this->formatted_version,
            'released_at' => $this->released_at?->format('Y-m-d'),
            'release_notes' => $this->release_notes,
        ];
    }
}