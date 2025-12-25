<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
            return [
                'name' => $this->s_name,
                'sku'=>$this->sku,
                'description' => $this->s_description,
                'price' => $this->price,
                'stock_quantity' => $this->stock_quantity,
                'category' => $this->category->s_name,
                'delivery_option' => $this->delivery_option,
                'tax_rate'=> $this->tax_rate,
                'discount_rate' => $this->discount_rate,
                'product_rate' => $this->product_rate,
                'status' => $this->status,
                'images' => ImageResource::collection($this->whenLoaded('images')),
                'created_at' => $this->created_at
            ];
        }
    }

