<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        // return parent::toArray($request);

        //* Cek apakah ini hasil manual join berdasarkan prefix unik
        if (isset($this->companies_company_id))
            return $this->mapJoinData();

        return $this->mapEloquentData();
    }

    /**
     * Mapping data khusus hasil Manual Join (Query Builder)
     */
    private function mapJoinData(): array {
        return [
            'company_id'   => $this->companies_company_id,
            'company_logo' => $this->companies_company_logo,
            'company_name' => $this->companies_company_name,
            'created_at' => $this->companies_created_at,
            'updated_at' => $this->companies_updated_at,
            'deleted_at' => $this->companies_deleted_at,
        ];
    }

    /**
     * Mapping data standar Eloquent Model
     */
    private function mapEloquentData(): array {
        return [
            'company_id' => $this->company_id,
            'company_logo' => $this->company_logo,
            'company_name' => $this->company_name,
            'company_address' => $this->company_address,
            'company_region' => [
                'province' => self::setRegionProvince($this->village?->district?->regency?->province),
                'regency' => self::setRegionRegency($this->village?->district?->regency),
                'district' => self::setRegionDistrict($this->village?->district),
                'village' => self::setRegionVillage($this->village)
            ],
            'company_zip_code' => $this->company_zip_code,
            'company_fax' => $this->company_fax,
            'company_phone' => $this->company_phone,
            'company_website' => $this->company_website,
            'company_email' => $this->company_email,
            'company_email_verified_at' => $this->company_email_verified_at,
            'company_is_status' => $this->company_is_status,
            'company_base_price' => $this->company_base_price,
            'company_billing_cycle' => $this->company_billing_cycle,
            'company_billing_status' => $this->company_billing_status,
            'company_trial_ends_at' => $this->company_trial_ends_at,
            'company_paid_ends_at' => $this->company_paid_ends_at,

            //* Relationship
            'company_detail' => $this->whenLoaded('details', fn() => new CompanyDetailResource($this->details)),
            'company_app_authentication' => $this->whenLoaded('appAuthentication', fn() => new CompanyAppAuthenticationResource($this->appAuthentication)),
            'company_invoices' => $this->whenLoaded('invoices', fn() => CompanyInvoiceResource::collection($this->invoices)),
            'company_events' => $this->whenLoaded('events', fn() => CompanyEventResource::collection($this->events)),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }

    private static function setRegionProvince($data = null) {
        return $data ? [
            'province_id' => $data->province_id,
            'province_name' => $data->province_name
        ] : null;
    }

    private static function setRegionRegency($data = null) {
        return $data ? [
            'regency_id' => $data->regency_id,
            'regency_province_id' => $data->regency_province_id,
            'regency_name' => $data->regency_name
        ] : null;
    }

    private static function setRegionDistrict($data = null) {
        return $data ? [
            'district_id' => $data->district_id,
            'district_regency_id' => $data->district_regency_id,
            'district_name' => $data->district_name
        ] : null;
    }

    private static function setRegionVillage($data = null) {
        return $data ? [
            'village_id'   => $data->village_id,
            'village_district_id' => $data->village_district_id,
            'village_name' => $data->village_name
        ] : null;
    }
}
