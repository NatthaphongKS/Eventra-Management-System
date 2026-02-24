<?php

namespace App\Http\Controllers;

use App\Service\HistoryServices\HistoryCategoryService;
use Illuminate\Http\JsonResponse;

class HistoryCategoryController extends Controller
{
    /**
     * @var HistoryCategoryService
     */
    private $historyCategoryService;

    public function __construct(HistoryCategoryService $historyCategoryService)
    {
        $this->historyCategoryService = $historyCategoryService;
    }

    public function index(): JsonResponse
    {
        $rows = $this->historyCategoryService->getAllHistoryCategories();

        return response()->json($rows);
    }
}
