<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'company_event_id' => $this->company_event_id,
            'company_event_company_id' => $this->company_event_company_id,
            'company_event_type' => $this->company_event_type,
            'company_event_description' => $this->company_event_description,
            'company_event_metadata' => $this->company_event_metadata,
            'company_event_status' => $this->company_event_status,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
