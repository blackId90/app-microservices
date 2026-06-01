<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait ApplyWithTrashedRelationScopes {

    /**
     * Scope untuk melakukan eager loading dengan withTrashed secara otomatis pada setiap level.
     */
    #[Scope]
    public function withTrashedRelations(Builder $query, array $relations = []): Builder {
        if (empty($relations))
            return $query;

        $eagerLoads = [];
        foreach ($relations as $rel) {
            $segments = explode('.', $rel);
            $currentPath = '';

            foreach ($segments as $segment) {
                $currentPath = $currentPath ? "$currentPath.$segment" : $segment;

                //* Daftarkan closure withTrashed untuk setiap level
                $eagerLoads[$currentPath] = function ($q) {
                    //* Gunakan method_exists pada model terkait relasi tersebut
                    if (method_exists($q->getModel(), 'runSoftDelete') || in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($q->getModel())))
                        $q->withTrashed();
                };
            }
        }

        return $query->with($eagerLoads);
    }
}
