<?php

namespace App\Enums;

enum TypeDeleteEnum {
    const SOFT_DELETE = 1; // soft delete (default)
    const RESTORE = 2; // restore from trash
    const PERMANENT_DELETE = 3; // force delete from trash (hard delete)
    const HARD_DELETE = 4; // permanent delete langsung (tanpa cek softdelete)
}
