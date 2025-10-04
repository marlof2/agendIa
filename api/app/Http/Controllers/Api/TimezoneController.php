<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Timezone;
use Illuminate\Http\JsonResponse;

class TimezoneController extends Controller
{
    /**
     * Get all timezones
     */
    public function index(): JsonResponse
    {
        try {
            $timezones = Timezone::orderBy('region')->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $timezones
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar fusos horários',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific timezone
     */
    public function show(Timezone $timezone): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $timezone
        ]);
    }
}
