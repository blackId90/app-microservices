<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\RestController;
use App\Services\Applications\AuthRolePermissionService;
use Illuminate\Http\Request;

class AuthRolePermissionController extends RestController {

    public function __construct(
        protected AuthRolePermissionService $authRolePermissionService
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {
        //
    }
}
