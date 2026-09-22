<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'price' => $this->price,
            'description' => $this->description,
            'category' => $this->category,
            'images' => $this->images,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'created_by_id' => $this->created_by_id,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
            'updated_by_id' => $this->updated_by_id,
        ];
    }
}
