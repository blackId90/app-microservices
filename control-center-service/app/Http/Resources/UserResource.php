<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        // return parent::toArray($request);
        return [
            'user_id' => $this->user_id,
            'user_auth_user_id' => $this->user_auth_user_id,
            'user_avatar' => $this->user_avatar,
            'user_first_name' => $this->user_first_name,
            'user_last_name' => $this->user_last_name,
            'user_gender' => $this->user_gender,
            'user_address' => $this->user_address,
            'user_region' => [
                'province' => self::setRegionProvince($this->village?->district?->regency?->province),
                'regency' => self::setRegionRegency($this->village?->district?->regency),
                'district' => self::setRegionDistrict($this->village?->district),
                'village' => self::setRegionVillage($this->village)
            ],
            'user_zip_code' => $this->user_zip_code,
            'user_phone' => $this->user_phone,

            //* Relationship
            'user_auth' => $this->setAuthUser(),
            'role' => $this->when($request->routeIs('read.users'), function () {
                return $this->relationLoaded('authRole') ? $this->authRole : null;
            }),
            'company' => $this->when($request->routeIs(['list.users', 'read.users']), $this->setCompanyUser()),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }

    /**
     * Combining manual JOIN and Eager Loading results
     * Become a standard object for the Resource API.
     */
    private function setAuthUser(): ?AuthUserResource {
        $dataSource = match (true) {
            isset($this->sync_auth_users_auth_user_id) => $this,
            $this->relationLoaded('authUser') && $this->authUser => $this->authUser,
            default => null
        };

        return $dataSource ? new AuthUserResource($dataSource) : null;
    }

    private function setCompanyUser(): ?CompanyResource {
        /*
        if (isset($this->companies_company_id))
            return new CompanyResource($this);

        if ($this->relationLoaded('company') && $this->company)
            return new CompanyResource($this->company);

        return null;
        */
        $dataSource = match (true) {
            isset($this->companies_company_id) => $this,
            $this->relationLoaded('company') && $this->company => $this->company,
            default => null
        };

        return $dataSource ? new CompanyResource($dataSource) : null;
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
