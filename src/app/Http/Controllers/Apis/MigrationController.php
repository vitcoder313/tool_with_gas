<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Services\MigrationService;

class MigrationController extends Controller
{
    //
    private MigrationService $migrationService;
    public function __construct()
    {
        $this->migrationService = new MigrationService();
    }

    public function migration(Request $request): JsonResponse
    {
        $this->migrationService->createMigration($request->all());
        return response()->json([
            'message' => 'Migration success'
        ]);
    }
}
