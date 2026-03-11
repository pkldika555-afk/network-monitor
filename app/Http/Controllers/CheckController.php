<?php

namespace App\Http\Controllers;

use App\Models\CheckLog;
use App\Models\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CheckController extends Controller
{
    public function single($id)
    {
        $service = Services::findOrFail($id);
        $result = $this->ping($service);
        $previousStatus = $service->status;

        $service->update([
            'status' => $result['status'],
            'response_ms' => $result['response_ms'],
            'last_checked_at' => now(),
        ]);

        if ($result['status'] !== $previousStatus || $previousStatus === 'unknown') {
            CheckLog::create([
                'service_id' => $service->id,
                'status' => $result['status'],
                'response_ms' => $result['response_ms'],
                'http_code' => $result['http_code'],
                'error_message' => $result['error_message'],
                'triggered_by' => 'manual',
                'checked_at' => now(),
            ]);
        }
    }
    public function all(string $triggeredBy = 'manual')
    {
        $services = Services::where('is_active', true)->get();
        $results = [];


        foreach ($services as $service) {
            $result = $this->ping($service);
            $previousStatus = $service->status;
            $service->update([
                'status' => $result['status'],
                'response_ms' => $result['response_ms'],
                'last_checked_at' => now(),
            ]);
            if ($result['status'] !== $previousStatus || $previousStatus === 'unknown') {
                CheckLog::create([
                    'service_id' => $service->id,
                    'status' => $result['status'],
                    'response_ms' => $result['response_ms'],
                    'http_code' => $result['http_code'],
                    'error_message' => $result['error_message'],
                    'triggered_by' => $triggeredBy,
                    'checked_at' => now(),
                ]);
            }

            $results[] = [
                'id' => $service->id,
                'name' => $service->name,
                'status' => $result['status'],
                'response_ms' => $result['response_ms'],
            ];
        }

        $online = collect($results)->where('status', 'online')->count();
        $offline = collect($results)->where('status', 'offline')->count();

        return response()->json([
            'total' => count($results),
            'online' => $online,
            'offline' => $offline,
            'results' => $results,
        ]);
    }
    private function ping(Services $service): array
    {
        $start = microtime(true);

        try {
            $http = Http::timeout(5)
                ->connectTimeout(3);

            // Tambahkan auth sesuai tipe
            if ($service->auth_type === 'bearer' && $service->auth_value) {
                $http = $http->withToken($service->auth_value);
            } elseif ($service->auth_type === 'basic' && $service->auth_value) {
                // format auth_value: "username:password"
                [$user, $pass] = explode(':', $service->auth_value, 2);
                $http = $http->withBasicAuth($user, $pass);
            }

            $response = $http->get($service->url);
            $ms = (int) round((microtime(true) - $start) * 1000);

            // Anggap online jika HTTP < 500
            // (beberapa IP camera return 401 tapi tetap hidup)
            $isOnline = $response->status() < 500;

            return [
                'status' => $isOnline ? 'online' : 'offline',
                'response_ms' => $ms,
                'http_code' => $response->status(),
                'error_message' => $isOnline ? null : 'HTTP ' . $response->status(),
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {

            $ms = (int) round((microtime(true) - $start) * 1000);
            return [
                'status' => 'offline',
                'response_ms' => null,
                'http_code' => 0,
                'error_message' => 'Timeout / tidak bisa dijangkau: ' . $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'offline',
                'response_ms' => null,
                'http_code' => 0,
                'error_message' => $e->getMessage(),
            ];
        }
    }
}
