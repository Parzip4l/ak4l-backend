<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class EptwPermitController extends Controller
{
    public function check(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'permit_number' => ['nullable', 'string', 'max:100'],
            'letter_number' => ['nullable', 'string', 'max:100'],
            'request_number' => ['nullable', 'string', 'max:100'],
        ]);

        $permitNumber = $request->input('permit_number')
            ?? $request->input('letter_number')
            ?? $request->input('request_number');

        if ($validator->fails() || $permitNumber == null || trim($permitNumber) == '') {
            return response()->json([
                'result' => false,
                'found' => false,
                'valid' => false,
                'message' => 'Nomor PTW atau nomor request wajib diisi',
                'errors' => $validator->errors(),
            ], 422);
        }

        $eptwBaseUrl = rtrim(env('EPTW_API_URL', 'http://127.0.0.1:8001'), '/');

        try {
            $response = Http::timeout(10)->post($eptwBaseUrl . '/api/permit/check', [
                'permit_number' => $permitNumber,
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'found' => false,
                    'valid' => false,
                    'message' => 'Layanan ePTW tidak dapat dihubungi',
                ], 502);
            }

            return response()->json($response->json());
        } catch (\Throwable $exception) {
            return response()->json([
                'result' => false,
                'found' => false,
                'valid' => false,
                'message' => 'Layanan ePTW tidak dapat dihubungi',
            ], 502);
        }
    }
}
