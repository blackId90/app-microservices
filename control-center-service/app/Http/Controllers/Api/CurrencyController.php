<?php

namespace App\Http\Controllers\Api;

use App\Enums\{AppAuthResponseCode, TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum, TypeUpdateEnum};
use App\Http\Controllers\RestController;
use App\Http\Requests\CurrencyStoreOrUpdateRequest;
use App\Http\Resources\CurrencyResource;
use App\Services\Applications\CurrencyService;
use Illuminate\Http\{JsonResponse, Request};
use Symfony\Component\HttpFoundation\Response;

class CurrencyController extends RestController {

    public function __construct(
        // protected AuthUserService $authUserService
        protected CurrencyService $currencyService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse {
        $search = $request->query('q');
        $perPage = (int) $request->query('limit', 10);
        $perPage = min($perPage, 100); // max 100
        $typeList = (int) $request->query('type_list', TypeBrowseEnum::WITHOUT_DELETED);

        $results = $this->currencyService->listCurrencies($search, $perPage, $typeList);

        //* Transform items to Resource
        $data = [
            'records' => CurrencyResource::collection($results->items()),
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
    public function store(CurrencyStoreOrUpdateRequest $request): JsonResponse {
        $validated = $request->validated();

        $result = $this->currencyService->createCurrency($validated);

        return $this->formatResponse(
            status: Response::HTTP_CREATED,
            message: AppAuthResponseCode::SuccessCreate->getMessage('success'),
            data: new CurrencyResource($result)
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $currencyId): JsonResponse {
        $typeRead = (int) $request->query('type_read', TypeReadEnum::WITHOUT_DELETED);

        $result = $this->currencyService->getCurrencyById($currencyId, $typeRead);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: new CurrencyResource($result)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CurrencyStoreOrUpdateRequest $request, string $currencyId): JsonResponse {
        $typeUpdate = (int) $request->query('type_update', TypeUpdateEnum::WITHOUT_DELETED);

        $result = $this->currencyService->updateCurrency($currencyId, $request->validated(), $typeUpdate);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: new CurrencyResource($result)
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $currencyId): JsonResponse {
        $typeDelete = (int) $request->query('type_delete', TypeDeleteEnum::SOFT_DELETE);

        $result = $this->currencyService->deleteCurrency($currencyId, $typeDelete);

        return $this->formatResponse(
            status: Response::HTTP_OK,
            message: $result['message']->getMessage('success')
        );
    }
}
