<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        // return parent::toArray($request);

        //* Cek apakah ini hasil manual join berdasarkan prefix unik
        if (isset($this->currencies_currency_id))
            return $this->mapJoinData();

        return $this->mapEloquentData();
    }

    /**
     * Mapping data khusus hasil Manual Join (Query Builder)
     */
    private function mapJoinData(): array {
        return [
            'currency_id' => $this->currencies_currency_id,
            'currency_code' => $this->currencies_currency_code,
            'currency_name' => $this->currencies_currency_name,
            'currency_symbol' => $this->currencies_currency_symbol,
            'currency_is_active' => $this->currencies_currency_is_active,
            'created_at' => $this->currencies_created_at,
            'updated_at' => $this->currencies_updated_at,
            'deleted_at' => $this->currencies_deleted_at
        ];
    }

    /**
     * Mapping data standar Eloquent Model
     */
    private function mapEloquentData(): array {
        return [
            'currency_id' => $this->currency_id,
            'currency_code' => $this->currency_code,
            'currency_name' => $this->currency_name,
            'currency_symbol' => $this->currency_symbol,
            'currency_is_active' => $this->currency_is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
