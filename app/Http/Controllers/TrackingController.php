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

        // ⚡ Bolt Optimization: Manually cache both successful and negative responses
        // to prevent repeated failed queries from bypassing Cache::remember exceptions
        // and exhausting rate limits.
        $cacheKey = "tracking_v2_{$resi}";
        $cached = Cache::get($cacheKey);

        if ($cached) {
            if (isset($cached['error'])) {
                throw new \Exception($cached['error']);
            }
            if (isset($cached['data'])) {
                $data = $cached['data'];

                return response()->json($data);
            }
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
            Cache::put($cacheKey, ['error' => $errorMessage], 600);
            throw new \Exception($errorMessage);
        }

        $data = $json['data'];
        Cache::put($cacheKey, ['data' => $data], 600);

        return response()->json($data);
    }
}
