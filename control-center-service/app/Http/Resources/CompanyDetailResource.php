<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyDetailResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        // return parent::toArray($request);
        return [
            'company_detail_company_id' => $this->company_detail_company_id,
            'company_detail_facebook' => $this->company_detail_facebook,
            'company_detail_twitter' => $this->company_detail_twitter,
            'company_detail_instagram' => $this->company_detail_instagram,
            'company_detail_linkedin' => $this->company_detail_linkedin,
            'company_detail_smtp_host' => $this->company_detail_smtp_host,
            'company_detail_smtp_port' => $this->company_detail_smtp_port,
            'company_detail_smtp_name' => $this->company_detail_smtp_name,
            'company_detail_smtp_user' => $this->company_detail_smtp_user,
            'company_detail_smtp_password' => $this->company_detail_smtp_password,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
