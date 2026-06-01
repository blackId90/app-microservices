<?php

namespace App\Enums;

enum TypeReadEnum {
    const WITHOUT_DELETED = 1; // tidak termasuk relasi terhapus (tapi untuk table sendiri, tanpa relasi)
    const WITH_DELETED = 2; // action null only soft-deleted, dan action update include soft-deleted records
}
