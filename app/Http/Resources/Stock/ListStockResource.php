<?php

namespace App\Http\Resources\Stock;

use Illuminate\Http\Resources\Json\JsonResource;

class ListStockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this['id'],

            'dealer' => $this['dealer'] != null ? $this['dealer'] : null,
            'car_brand' => $this['car_brand'] != null ? $this['car_brand'] : null,
            'car_model' => $this['car_model'] != null ? $this['car_model'] : null,

            'stocks' => $this['stocks']
        ];
    }
}
