<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TrackingController extends Controller
{
    public function track(Request $request)
    {
        $request->validate([
            'resi' => 'required|string|alpha_dash|min:6|max:50'
        ]);

        $resi = strtoupper($request->resi);
        
        // Cache result for 10 minutes to avoid hitting BinderByte rate limits
        $data = Cache::remember("tracking_{$resi}", 600, function () use ($resi) {
            $apiKey = env('BINDERBYTE_API_KEY');
            
            if (empty($apiKey)) {
                throw new \Exception('BinderByte API key is not configured.');
            }

            $response = Http::withOptions(['verify' => false])->timeout(12)->get('https://api.binderbyte.com/v1/track', [
                'api_key' => $apiKey,
                'courier' => 'spx',
                'awb' => $resi
            ]);

            if ($response->failed()) {
                throw new \Exception("Gagal menghubungi server pelacakan.");
            }

            $json = $response->json();
            
            if (!isset($json['status']) || $json['status'] !== 200) {
                throw new \Exception($json['message'] ?? 'Resi tidak ditemukan.');
            }

            return $json['data'];
        });

        return response()->json($data);
    }
}
