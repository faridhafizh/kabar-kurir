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
        // Cache::remember doesn't cache when an exception is thrown. We manually get and put
        // the payload to cache expected errors (e.g., 'Resi tidak ditemukan') and avoid rate limits
        // on repeated invalid checks. Key versioned to _v2 due to payload structure change.
        $cacheKey = "tracking_{$resi}_v2";
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            if (isset($cached['error'])) {
                // Re-throw cached error to utilize native exception handler
                throw new \Exception($cached['error']);
            }

            return response()->json($cached['data']);
        }

        try {
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
                throw new \Exception($json['message'] ?? 'Resi tidak ditemukan.');
            }

            $data = $json['data'];
            // Cache successful payload for 10 minutes
            Cache::put($cacheKey, ['data' => $data], 600);

            return response()->json($data);

        } catch (\Exception $e) {
            // Cache error response payload for 10 minutes (negative cache)
            Cache::put($cacheKey, ['error' => $e->getMessage()], 600);
            throw $e;
        }
    }
}
