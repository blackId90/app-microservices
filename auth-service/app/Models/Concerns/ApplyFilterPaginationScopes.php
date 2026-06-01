<?php

namespace App\Models\Concerns;

use App\Enums\TypeBrowseEnum;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait ApplyFilterPaginationScopes {

    /**
     * Scope for soft delete filter of main table based on enum TypeBrowseEnum.
     *
     * @param Builder $query
     * @param integer $typeList
     * @return Builder
     */
    #[Scope]
    public function withFilterTypeList(Builder $query, int $typeList): Builder {
        return match ($typeList) {
            TypeBrowseEnum::DELETED_ONLY => $query->onlyTrashed(),
            TypeBrowseEnum::ALL_DATA => $query->withTrashed(),
            default => $query,
        };
    }

    /**
     * Scope for dynamic search (table Main & table Relation) with validation.
     * Example fields: 'main.name', 'relation.name'
     *
     * @param Builder $query
     * @param string|null $term
     * @param array $searchableColumns
     * @return Builder
     */
    #[Scope]
    public function filterSearch(Builder $query, ?string $term, array $searchableColumns = []): Builder {
        if (!$term || empty($searchableColumns))
            return $query;

        return $query->where(function ($subQuery) use ($term, $searchableColumns) {
            foreach ($searchableColumns as $column) {
                $subQuery->orWhere($column, 'ILIKE', "%{$term}%");
            }
        });
    }

    /**
     * Scope untuk filter whereIn yang mendukung manual join dan Eloquent relationship.
     * Contoh format array input:
     * [
     *   'auth_permissions.auth_permission_type' => [1, 2, 3], // Manual Join / Main Table
     *   'parent.auth_permission_type' => [4, 5] // Eloquent Relation with('parent)
     * ]
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    #[Scope]
    public function filterWhereIn(Builder $query, array $filters = []): Builder {
        if (empty($filters))
            return $query;

        foreach ($filters as $column => $values) {
            //* 1. Validasi awal: pastikan value adalah array dan tidak kosong []
            if (!is_array($values) || empty($values))
                continue;

            //* 2. Bersihkan array dari nilai kosong seperti '', null, atau spasi kosong
            $cleanedValues = array_filter($values, function ($value) {
                if (is_null($value))
                    return false;

                //* Menghapus string kosong atau string yang hanya berisi spasi
                return trim((string) $value) !== '';
            });

            //* 3. Jika setelah dibersihkan nilainya habis (misal input awal ['']), lewati kolom ini
            if (empty($cleanedValues))
                continue;

            //* Re-index array untuk merapikan key indeks setelah proses filter
            $cleanedValues = array_values($cleanedValues);

            //* 4. Eksekusi query berdasarkan format kolom
            if (str_contains($column, '.')) {
                [$relationOrTable, $actualColumn] = explode('.', $column, 2);

                //* Deteksi Eloquent Relationship
                if (method_exists($query->getModel(), $relationOrTable)) {
                    $query->whereHas($relationOrTable, function ($subQuery) use ($actualColumn, $cleanedValues) {
                        $subQuery->whereIn($actualColumn, $cleanedValues);
                    });
                } else {
                    //* Deteksi Manual Join / Table Alias
                    $query->whereIn($column, $cleanedValues);
                }
            } else {
                //* Tabel Utama
                $query->whereIn($column, $cleanedValues);
            }
        }

        return $query;
    }
}
