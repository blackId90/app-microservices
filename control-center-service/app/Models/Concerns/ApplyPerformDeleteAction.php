<?php

namespace App\Models\Concerns;

use App\Enums\TypeDeleteEnum;

trait ApplyPerformDeleteAction {

    /**
     * Action delete or restore by TypeDeleteEnum.
     *
     * @param integer $typeDelete
     * @return boolean
     */
    public function performDeleteAction(int $typeDelete): bool {
        return match ($typeDelete) {
            //* Restoring soft deleted
            TypeDeleteEnum::RESTORE => $this->checkSoftDeleteSupport() ? $this->restore() : false,

            //* Permanently deleting from database
            TypeDeleteEnum::PERMANENT_DELETE, TypeDeleteEnum::HARD_DELETE => $this->forceDeleteAction(),

            //* Soft Delete
            default => $this->delete(),
        };
    }

    /**
     * Memastikan forceDelete berjalan baik dengan atau tanpa trait SoftDeletes.
     */
    protected function forceDeleteAction(): bool {
        //* Jika model pakai SoftDeletes, gunakan forceDelete()
        if ($this->checkSoftDeleteSupport())
            return $this->forceDelete();

        //* Jika model biasa (tanpa SoftDeletes), forceDelete sama dengan delete biasa
        return $this->delete();
    }

    /**
     * Cek apakah model ini menggunakan trait SoftDeletes Laravel.
     */
    protected function checkSoftDeleteSupport(): bool {
        return method_exists($this, 'runSoftDelete');
    }
}
