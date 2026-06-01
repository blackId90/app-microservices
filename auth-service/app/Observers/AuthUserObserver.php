<?php

namespace App\Observers;

use App\Models\AuthUser;
use App\Services\UserCacheService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class AuthUserObserver implements ShouldHandleEventsAfterCommit {

    public function __construct(
        protected UserCacheService $userCache
    ) {}

    /**
     * Handle the AuthUser "created" event.
     */
    public function created(AuthUser $user): void {
        //* Cache new user
        // $this->userCache->cacheUser($user);

        //* Send sync Auth Users to Control Center Service
        $user->sync('upsert');
    }

    /**
     * Handle the AuthUser "updated" event.
     */
    public function updated(AuthUser $user): void {
        //* Jika restore data, abaikan method ini. Biarkan method restored yang menangani logikanya
        if ($user->wasChanged('deleted_at') && $user->deleted_at === null)
            return;

        //* Invalidate old cache
        $this->userCache->invalidateUser($user->getKey());

        //* Cache updated user
        $this->userCache->cacheUser($user);

        //* Send sync Auth Users to Control Center Service
        $user->sync('upsert');
    }

    public function deleting(AuthUser $user) {
        if ($user->isForceDeleting())
            $user->is_being_force_deleted = true;
    }

    /**
     * Handle the AuthUser "deleted" event.
     */
    public function deleted(AuthUser $user): void {
        //* Jika ini adalah force delete, abaikan method ini. Biarkan method forceDeleted() yang menangani logikanya.
        if ($user->isForceDeleting() || (isset($user->is_being_force_deleted) && $user->is_being_force_deleted === true))
            return;

        //* Remove from cache
        $this->userCache->invalidateUser($user->getKey());

        //* Send sync Auth Users to Control Center Service
        $user->sync('soft_delete');
    }

    /**
     * Handle the AuthUser "restored" event.
     */
    public function restored(AuthUser $user): void {
        //* Cache restored user
        $this->userCache->cacheUser($user);

        //* Send sync Auth Users to Control Center Service
        $user->sync('restore');
    }

    /**
     * Handle the AuthUser "force deleted" event.
     */
    public function forceDeleted(AuthUser $user): void {
        //* Remove from cache
        $this->userCache->invalidateUserCacheOnly($user->getKey());

        //* Send sync Auth Users to Control Center Service
        $user->sync('force_delete');
    }
}
