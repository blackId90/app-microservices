<?php

namespace App\Http\Controllers\Api;

use App\Enums\{AppAuthResponseCode, TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum, TypeUpdateEnum};
use App\Http\Controllers\RestController;
use App\Http\Requests\ContinentStoreOrUpdate;
use App\Http\Resources\ContinentResource;
use App\Services\Applications\ContinentService;
use Illuminate\Http\{JsonResponse, Request};
use Symfony\Component\HttpFoundation\Response;

class ContinentController extends RestController {

    public function __construct(
        protected ContinentService $continentService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse {
        $search = $request->query('q');
        $perPage = (int) $request->query('limit', 10);
        $perPage = min($perPage, 100); // max 100
        $typeList = (int) $request->query('type_list', TypeBrowseEnum::WITHOUT_DELETED);

        $results = $this->continentService->listContinents($search, $perPage, $typeList);

        //* Transform items to Resource
        $data = [
            'records' => ContinentResource::collection($results->items()),
            'pagination' => [
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage()
            ]
        ];

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $data
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContinentStoreOrUpdate $request): JsonResponse {
        $validated = $request->validated();

        $result = $this->continentService->createContinent($validated);

        return $this->formatResponse(
            status: Response::HTTP_CREATED,
            message: AppAuthResponseCode::SuccessCreate->getMessage('success'),
            data: new ContinentResource($result)
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $continentId): JsonResponse {
        $typeRead = (int) $request->query('type_read', TypeReadEnum::WITHOUT_DELETED);

        $result = $this->continentService->getContinentById($continentId, $typeRead);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: new ContinentResource($result)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContinentStoreOrUpdate $request, string $continentId): JsonResponse {
        $typeUpdate = (int) $request->query('type_update', TypeUpdateEnum::WITHOUT_DELETED);

        $result = $this->continentService->updateContinent($continentId, $request->validated(), $typeUpdate);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: new ContinentResource($result)
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $continentId): JsonResponse {
        $typeDelete = (int) $request->query('type_delete', TypeDeleteEnum::SOFT_DELETE);

        $result = $this->continentService->deleteContinent($continentId, $typeDelete);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: $result['message']->getMessage('success')
        );
    }
}
