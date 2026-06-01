<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyInvoiceResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        // return parent::toArray($request);
        return [
            'company_invoice_id' => $this->company_invoice_id,
            'company_invoice_company_id' => $this->company_invoice_company_id,
            'company_invoice_no_inv' => $this->company_invoice_no_inv,
            'company_invoice_amount' => $this->company_invoice_amount,
            'company_invoice_months_paid' => $this->company_invoice_months_paid,
            'company_invoice_payment_method' => $this->company_invoice_payment_method,
            'company_invoice_paid_at' => $this->company_invoice_paid_at,
            'company_invoice_valid_until' => $this->company_invoice_valid_until,
            'company_invoice_status' => $this->company_invoice_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
