<?php

namespace App\Traits;

use DateTimeInterface;

trait DatetimeFormatter {
    protected function serializeDate(DateTimeInterface $date): string {
        // return $date->format('Y-m-d H:i:s.u');
        return $date->format('Y-m-d\TH:i:s.u\Z'); // ISO 8601
    }
}
