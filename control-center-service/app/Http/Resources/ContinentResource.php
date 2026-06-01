<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContinentResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        // return parent::toArray($request);

        //* Cek apakah ini hasil manual join berdasarkan prefix unik
        if (isset($this->continents_continent_id))
            return $this->mapJoinData();

        return $this->mapEloquentData();
    }

    /**
     * Mapping data khusus hasil Manual Join (Query Builder)
     */
    private function mapJoinData(): array {
        return [
            'continent_id' => $this->continents_continent_id,
            'continent_code' => $this->continents_continent_code,
            'continent_name' => $this->continents_continent_name,
            'created_at' => $this->continents_created_at,
            'updated_at' => $this->continents_updated_at,
            'deleted_at' => $this->continents_deleted_at
        ];
    }

    /**
     * Mapping data standar Eloquent Model
     */
    private function mapEloquentData(): array {
        return [
            'continent_id' => $this->continent_id,
            'continent_code' => $this->continent_code,
            'continent_name' => $this->continent_name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
