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

        // ⚡ Bolt Optimization: Implement negative caching to prevent rate limit exhaustion
        // on repeated invalid requests, and use versioned cache key to prevent deployment regressions.
        $cacheKey = "tracking_{$resi}_v2";
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            if (isset($cached['error'])) {
                return response()->json(['message' => $cached['error']], 400);
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
            Cache::put($cacheKey, ['data' => $data], 600);

            return response()->json($data);
        } catch (\Exception $e) {
            Cache::put($cacheKey, ['error' => $e->getMessage()], 600);

            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
