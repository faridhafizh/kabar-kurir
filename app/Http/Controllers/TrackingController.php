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

        // ⚡ Bolt Optimization: Implement negative caching for tracking requests.
        // Instead of using Cache::remember (which ignores caching if an exception is thrown),
        // we explicitly cache both success and error responses to protect the API rate limit.
        $cacheKey = "tracking_{$resi}_v2";

        $cached = Cache::get($cacheKey);

        if ($cached) {
            if (isset($cached['error'])) {
                // Rethrow the cached exception
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

            // Cache successful result for 10 minutes
            Cache::put($cacheKey, ['data' => $data], 600);

        } catch (\Exception $e) {
            // Cache the error for 10 minutes to prevent rate-limit exhaustion from repeated invalid requests
            Cache::put($cacheKey, ['error' => $e->getMessage()], 600);
            throw $e;
        }

        return response()->json($data);
    }
}
