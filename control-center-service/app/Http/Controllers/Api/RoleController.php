<?php

namespace App\Http\Controllers\Api;

use App\Enums\{AppAuthResponseCode, TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum, TypeUpdateEnum};
use App\Http\Controllers\RestController;
use App\Http\Requests\RoleStoreOrUpdateRequest;
use App\Services\Applications\RoleService;
use Illuminate\Http\{JsonResponse, Request};
use Symfony\Component\HttpFoundation\Response;

class RoleController extends RestController {

    public function __construct(
        protected RoleService $roleService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse {
        $search = $request->query('q');
        $perPage = (int) $request->query('limit', 10);
        $perPage = min($perPage, 100); // max 100
        $typeList = (int) $request->query('type_list', TypeBrowseEnum::WITHOUT_DELETED);

        $results = $this->roleService->listRoles($search, $perPage, $typeList);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $results
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleStoreOrUpdateRequest $request): JsonResponse {
        $validated = $request->validated();

        $result = $this->roleService->createRole($validated);

        return $this->formatResponse(
            status: Response::HTTP_CREATED,
            message: AppAuthResponseCode::SuccessCreate->getMessage('success'),
            data: $result
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $authRoleId): JsonResponse {
        $typeRead = (int) $request->query('type_read', TypeReadEnum::WITHOUT_DELETED);

        $result = $this->roleService->getRoleById($authRoleId, $typeRead);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $result
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleStoreOrUpdateRequest $request, string $authRoleId): JsonResponse {
        $typeUpdate = (int) $request->query('type_update', TypeUpdateEnum::WITHOUT_DELETED);

        $result = $this->roleService->updateRole($authRoleId, $request->validated(), $typeUpdate);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $result
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $authRoleId): JsonResponse {
        $typeDelete = (int) $request->query('type_delete', TypeDeleteEnum::SOFT_DELETE);

        $result = $this->roleService->deleteRole($authRoleId, $typeDelete);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: $result['message']->getMessage('success')
        );
    }

    public function optionPermissions(Request $request): JsonResponse {
        $search = $request->query('q');

        $results = $this->roleService->optionPermissions($search);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $results
        );
    }
}
