<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        // return parent::toArray($request);
        // dd($this);
        return [
            'country_id' => $this->country_id,
            'country_code' => $this->country_code,
            'country_alpha_3' => $this->country_alpha_3,
            'country_name' => $this->country_name,
            'country_capital' => $this->country_capital,
            'country_phone' => $this->country_phone,
            // 'country_continent_code' => $this->country_continent_code,
            // 'country_currency_code' => $this->country_currency_code,

            //* Relationship
            'country_continent' => $this->setContinent(),
            'country_currency' => $this->setCurrency(),

            //* Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }

    /**
     * Combining manual JOIN and Eager Loading results
     * Become a standard object for the Resource API.
     */
    private function setContinent(): ?ContinentResource {
        $dataSource = match (true) {
            isset($this->continents_continent_id) => $this,
            $this->relationLoaded('continent') && $this->continent => $this->continent,
            default => null
        };

        return $dataSource ? new ContinentResource($dataSource) : null;
    }

    /**
     * Combining manual JOIN and Eager Loading results
     * Become a standard object for the Resource API.
     */
    private function setCurrency(): ?CurrencyResource {
        $dataSource = match (true) {
            isset($this->currencies_currency_id) => $this,
            $this->relationLoaded('currency') && $this->currency => $this->currency,
            default => null
        };

        return $dataSource ? new CurrencyResource($dataSource) : null;
    }
}
