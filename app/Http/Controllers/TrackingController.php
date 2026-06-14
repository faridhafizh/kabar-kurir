<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TrackingController extends Controller
{
    public function track(Request $request)
    {
        $request->validate([
            'resi' => 'required|string|alpha_dash|min:6|max:50',
        ]);

        $resi = strtoupper($request->resi);

        // ⚡ Bolt Optimization: Replace Cache::remember with get/put to implement negative caching.
        // This caches 'not found' responses, preventing rate-limit exhaustion on repeated invalid tracking queries.
        $cacheKey = "tracking_{$resi}";
        $cachedData = Cache::get($cacheKey);

        if ($cachedData !== null) {
            if (isset($cachedData['is_error']) && $cachedData['is_error'] === true) {
                throw new \Exception($cachedData['message']);
            }

            return response()->json($cachedData);
        }

        $apiKey = env('BINDERBYTE_API_KEY');

        if (empty($apiKey)) {
            throw new \Exception('BinderByte API key is not configured.');
        }

        $response = Http::withOptions(['verify' => false])->timeout(12)->get('https://api.binderbyte.com/v1/track', [
            'api_key' => $apiKey,
            'courier' => 'spx',
            'awb' => $resi,
        ]);

        if ($response->failed()) {
            throw new \Exception('Gagal menghubungi server pelacakan.');
        }

        $json = $response->json();

        if (! isset($json['status']) || $json['status'] !== 200) {
            $errorMessage = $json['message'] ?? 'Resi tidak ditemukan.';
            Cache::put($cacheKey, ['is_error' => true, 'message' => $errorMessage], 600);
            throw new \Exception($errorMessage);
        }

        Cache::put($cacheKey, $json['data'], 600);

        return response()->json($json['data']);
    }
}
