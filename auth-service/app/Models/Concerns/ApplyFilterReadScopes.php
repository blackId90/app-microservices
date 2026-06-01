<?php

namespace App\Models\Concerns;

use App\Enums\TypeReadEnum;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait ApplyFilterReadScopes {

    /**
     * Scope for filter detail data (single record) by TypeReadEnum.
     *
     * @param Builder $query
     * @param integer $typeRead
     * @param boolean $withTrash
     * @return Builder
     */
    #[Scope]
    public function withFilterRead(Builder $query, int $typeRead, bool $withTrash): Builder {
        if ($typeRead === TypeReadEnum::WITH_DELETED)
            $withTrash ? $query->withTrashed() : $query->onlyTrashed();

        return $query;
    }
}
