<?php

namespace App\Models;

use App\Traits\DatetimeFormatter;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Outbox extends Model {
    use HasUuids, DatetimeFormatter;

    protected $table = 'outboxes';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $dateFormat = 'Y-m-d\TH:i:s.u\Z'; // ISO 8601

    protected $fillable = [
        'topic',
        'payload',
        'processed_at',
    ];

    /**
     * Casting payload agar otomatis menjadi array/object saat diakses
     */
    protected function casts(): array {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime:Y-m-d H:i:s.u',
            'created_at' => 'datetime:Y-m-d H:i:s.u',
            'updated_at' => 'datetime:Y-m-d H:i:s.u',
            'deleted_at' => 'datetime:Y-m-d H:i:s.u',
        ];
    }
}
