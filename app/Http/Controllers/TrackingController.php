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
        $cacheKey = "tracking_{$resi}";

        // ⚡ Bolt Optimization: Manually check cache to enable "Negative Caching".
        // Cache::remember throws an exception on invalid resi, bypassing the cache.
        // We cache the error response to prevent rate-limit exhaustion from repeated invalid requests.
        $cachedData = Cache::get($cacheKey);

        if ($cachedData !== null) {
            if (isset($cachedData['error'])) {
                return response()->json(['message' => $cachedData['error']], 400);
            }

            return response()->json($cachedData);
        }

        $apiKey = env('BINDERBYTE_API_KEY');

        if (empty($apiKey)) {
            return response()->json(['message' => 'BinderByte API key is not configured.'], 500);
        }

        $response = Http::withOptions(['verify' => false])->timeout(12)->get('https://api.binderbyte.com/v1/track', [
            'api_key' => $apiKey,
            'courier' => 'spx',
            'awb' => $resi,
        ]);

        if ($response->failed()) {
            return response()->json(['message' => 'Gagal menghubungi server pelacakan.'], 500);
        }

        $json = $response->json();

        if (! isset($json['status']) || $json['status'] !== 200) {
            $errorMessage = $json['message'] ?? 'Resi tidak ditemukan.';
            Cache::put($cacheKey, ['error' => $errorMessage], 600);

            return response()->json(['message' => $errorMessage], 400);
        }

        Cache::put($cacheKey, $json['data'], 600);

        return response()->json($json['data']);
    }
}
