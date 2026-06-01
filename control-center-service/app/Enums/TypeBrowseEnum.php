<?php

namespace App\Enums;

enum TypeBrowseEnum {
    const WITHOUT_DELETED = 1; // only active
    const DELETED_ONLY = 2; // only soft-deleted
    const ALL_DATA = 3; // both active & deleted
}
