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

        // ⚡ Bolt Optimization: Implement negative caching for tracking queries
        // Avoids `Cache::remember` closure which bypasses caching on Exception.
        // Explicitly stores successful results and expected 404/invalid errors
        // to prevent repetitive rate-limit exhaustion from bad tracking numbers.
        $cacheKey = "tracking_{$resi}_v2";
        $cachedValue = Cache::get($cacheKey);

        if ($cachedValue !== null) {
            if (isset($cachedValue['error'])) {
                return response()->json(['message' => $cachedValue['error']], 400);
            }

            return response()->json($cachedValue['data']);
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
            $errorMsg = $json['message'] ?? 'Resi tidak ditemukan.';
            Cache::put($cacheKey, ['error' => $errorMsg], 600);

            return response()->json(['message' => $errorMsg], 400);
        }

        Cache::put($cacheKey, ['data' => $json['data']], 600);

        return response()->json($json['data']);
    }
}
