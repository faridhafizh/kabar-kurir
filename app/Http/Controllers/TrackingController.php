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

        // ⚡ Bolt Optimization: Use negative caching for 10 minutes to avoid hitting BinderByte rate limits
        // on invalid requests (e.g. repeated bad AWB numbers). Cache::remember ignores thrown exceptions.
        $cacheKey = "tracking_{$resi}_v2";
        $cached = Cache::get($cacheKey);

        if ($cached) {
            if (isset($cached['error'])) {
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
            Cache::put($cacheKey, ['error' => 'Gagal menghubungi server pelacakan.'], 600);
            throw new \Exception('Gagal menghubungi server pelacakan.');
        }

        $json = $response->json();

        if (! isset($json['status']) || $json['status'] !== 200) {
            $errorMessage = $json['message'] ?? 'Resi tidak ditemukan.';
            Cache::put($cacheKey, ['error' => $errorMessage], 600);
            throw new \Exception($errorMessage);
        }

        Cache::put($cacheKey, ['data' => $json['data']], 600);

        return response()->json($json['data']);
    }
}
