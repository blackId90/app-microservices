<?php

namespace App\Enums;

enum TypeUpdateEnum {
    const WITHOUT_DELETED = 1; // hanya update active record
    const WITH_DELETED = 2; // boleh update termasuk soft-deleted
}
