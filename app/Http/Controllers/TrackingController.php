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

        // ⚡ Bolt Optimization: Implement explicit negative caching.
        // If we throw an exception inside Cache::remember(), it bypasses the cache entirely.
        // By using Cache::get and explicit Cache::put, we can cache both successful responses
        // and expected error states (like 'Resi tidak ditemukan') to prevent rate-limit
        // exhaustion from repeated invalid tracking number requests.
        $cacheKey = "tracking_v2_{$resi}";
        $cachedData = Cache::get($cacheKey);

        if ($cachedData !== null) {
            // Check if it's a negative cache (stored exception)
            if (isset($cachedData['error'])) {
                throw new \Exception($cachedData['error']);
            }

            return response()->json($cachedData['data']);
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
            // We do NOT cache server/network failures, only "not found" type logical errors
            throw new \Exception('Gagal menghubungi server pelacakan.');
        }

        $json = $response->json();

        if (! isset($json['status']) || $json['status'] !== 200) {
            $errorMessage = $json['message'] ?? 'Resi tidak ditemukan.';
            // Negative cache the error for 10 minutes
            Cache::put($cacheKey, ['error' => $errorMessage], 600);
            throw new \Exception($errorMessage);
        }

        // Cache the successful data for 10 minutes
        Cache::put($cacheKey, ['data' => $json['data']], 600);

        return response()->json($json['data']);
    }
}
