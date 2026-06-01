<?php

namespace App\Http\Controllers\Api;

use App\Enums\{AppAuthResponseCode, TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum, TypeUpdateEnum};
use App\Http\Controllers\RestController;
use App\Http\Requests\AuthRoleStoreOrUpdateRequest;
use App\Http\Resources\{AuthPermissionResource, AuthRoleResource};
use App\Services\Applications\{AuthPermissionService, AuthRoleService};
use Illuminate\Http\{JsonResponse, Request};
use Symfony\Component\HttpFoundation\Response;

class AuthRoleController extends RestController {

    public function __construct(
        protected AuthRoleService $authRoleService,
        protected AuthPermissionService $authPermissionService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse {
        $search = $request->query('q');
        $perPage = (int) $request->query('limit', 10);
        $perPage = min($perPage, 100); // max 100
        $typeList = (int) $request->query('type_list', TypeBrowseEnum::WITHOUT_DELETED);

        $roles = $this->authRoleService->listAuthRoles($search, $perPage, $typeList);

        //* Transform items to Resource
        $data = [
            'records' => AuthRoleResource::collection($roles->items()),
            'pagination' => [
                'total' => $roles->total(),
                'per_page' => $roles->perPage(),
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage()
            ]
        ];

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->value,
            data: $data
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AuthRoleStoreOrUpdateRequest $request): JsonResponse {
        $role = $this->authRoleService->createRole($request->validated());

        return $this->formatResponse(
            status: Response::HTTP_CREATED,
            message: AppAuthResponseCode::SuccessCreate->value,
            data: new AuthRoleResource($role)
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $authRoleId): JsonResponse {
        $typeRead = (int) $request->query('type_read', TypeReadEnum::WITHOUT_DELETED);

        $role = $this->authRoleService->getRoleById($authRoleId, $typeRead, ['rolePermissions.permission.parent']);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->value,
            data: new AuthRoleResource($role)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AuthRoleStoreOrUpdateRequest $request, string $authRoleId): JsonResponse {
        $typeUpdate = (int) $request->query('type_update', TypeUpdateEnum::WITHOUT_DELETED);

        $role = $this->authRoleService->updateRole($authRoleId, $request->validated(), $typeUpdate);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessUpdate->value,
            data: new AuthRoleResource($role)
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $authRoleId): JsonResponse {
        $typeDelete = (int) $request->query('type_delete', TypeDeleteEnum::SOFT_DELETE);

        $result = $this->authRoleService->deleteRole($authRoleId, $typeDelete);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: $result['message']->value
        );
    }

    public function optionPermissions(Request $request): JsonResponse {
        $search = $request->query('q');
        $perPage = 10;
        $typeList = TypeBrowseEnum::WITHOUT_DELETED;
        $filterWhereIn = ['auth_permissions.auth_permission_type' => ['header', 'group', 'parent']];

        $results = $this->authPermissionService->optionPermissions($search, $perPage, $typeList, $filterWhereIn);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->value,
            data: AuthPermissionResource::collection($results->items())
        );
    }
}
