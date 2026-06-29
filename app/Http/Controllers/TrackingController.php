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

        // ⚡ Bolt Optimization: Implement negative caching.
        // Instead of using Cache::remember (which bypasses caching if an exception is thrown inside the closure),
        // we manually check Cache::get() and use Cache::put() to cache BOTH successful responses and errors.
        // This prevents repeated API calls for invalid requests from exhausting rate limits.
        // Cache key is versioned to v2 to avoid collisions with old payloads.
        $cacheKey = "tracking_v2_{$resi}";
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            if (isset($cached['error'])) {
                // Re-throw cached error to let the native exception handler format it
                throw new \Exception($cached['error']);
            }

            return response()->json($cached['data']);
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
            $errorMsg = 'Gagal menghubungi server pelacakan.';
            Cache::put($cacheKey, ['error' => $errorMsg], 600);
            throw new \Exception($errorMsg);
        }

        $json = $response->json();

        if (! isset($json['status']) || $json['status'] !== 200) {
            $errorMsg = $json['message'] ?? 'Resi tidak ditemukan.';
            Cache::put($cacheKey, ['error' => $errorMsg], 600);
            throw new \Exception($errorMsg);
        }

        $data = $json['data'];
        Cache::put($cacheKey, ['data' => $data], 600);

        return response()->json($data);
    }
}
