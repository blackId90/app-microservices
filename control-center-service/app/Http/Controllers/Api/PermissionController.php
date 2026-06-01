<?php

namespace App\Http\Controllers\Api;

use App\Enums\{AppAuthResponseCode, TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum, TypeUpdateEnum};
use App\Http\Controllers\RestController;
use App\Http\Requests\PermissionStoreOrUpdateRequest;
use App\Services\Applications\PermissionService;
use Illuminate\Http\{JsonResponse, Request};
use Symfony\Component\HttpFoundation\Response;

class PermissionController extends RestController {

    public function __construct(
        protected PermissionService $permissionService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse {
        $search = $request->query('q');
        $perPage = (int) $request->query('limit', 10);
        $perPage = min($perPage, 100); // max 100
        $typeList = (int) $request->query('type_list', TypeBrowseEnum::WITHOUT_DELETED);

        $results = $this->permissionService->listPermissions($search, $perPage, $typeList);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $results
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PermissionStoreOrUpdateRequest $request): JsonResponse {
        $permission = $this->permissionService->createPermission($request->validated());

        return $this->formatResponse(
            status: Response::HTTP_CREATED,
            message: AppAuthResponseCode::SuccessCreate->value,
            data: $permission
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $authPermissionId): JsonResponse {
        $typeRead = (int) $request->query('type_read', TypeReadEnum::WITHOUT_DELETED);

        $result = $this->permissionService->getPermissionById($authPermissionId, $typeRead);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $result
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PermissionStoreOrUpdateRequest $request, string $authPermissionId): JsonResponse {
        $typeUpdate = (int) $request->query('type_update', TypeUpdateEnum::WITHOUT_DELETED);

        $result = $this->permissionService->updatePermission($authPermissionId, $request->validated(), $typeUpdate);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $result
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $authPermissionId): JsonResponse {
        $typeDelete = (int) $request->query('type_delete', TypeDeleteEnum::SOFT_DELETE);

        $result = $this->permissionService->deletePermission($authPermissionId, $typeDelete);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: $result['message']->getMessage('success')
        );
    }

    public function optionPermissions(Request $request): JsonResponse {
        $search = $request->query('q');

        $results = $this->permissionService->optionPermissions($search);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $results
        );
    }
}
