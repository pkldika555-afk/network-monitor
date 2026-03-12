<?php

namespace App\Http\Controllers;

use App\Models\CheckLog;
use App\Models\Services;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;

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

    if ($services->isEmpty()) {
        return response()->json(['total' => 0, 'online' => 0, 'offline' => 0, 'results' => []]);
    }

    $responses = Http::pool(fn (Pool $pool) =>
        $services->map(fn ($service) =>
            $this->buildRequest($pool, $service)->get($service->url)
        )
    );

    $results      = [];
    $logsToInsert = [];

    DB::transaction(function () use ($services, $responses, $triggeredBy, &$results, &$logsToInsert) {
        foreach ($services as $index => $service) {
            $response       = $responses[$index];
            $previousStatus = $service->status;

            $isOnline     = false;
            $httpCode     = 0;
            $errorMsg     = 'Timeout / Unreachable';
            $responseTime = null;

            if ($response instanceof \Illuminate\Http\Client\Response) {
                $httpCode     = $response->status();
                $isOnline     = $httpCode < 500;
                $errorMsg     = $isOnline ? null : "HTTP $httpCode";
                $responseTime = (int) round(($response->transferStats?->getTransferTime() ?? 0) * 1000);
            } elseif ($response instanceof \Exception) {
                $errorMsg = substr($response->getMessage(), 0, 255);
            }

            $currentStatus = $isOnline ? 'online' : 'offline';

            $service->update([
                'status'          => $currentStatus,
                'response_ms'     => $responseTime,
                'last_checked_at' => now(),
            ]);

            if ($currentStatus !== $previousStatus || $previousStatus === 'unknown') {
                $logsToInsert[] = [
                    'service_id'    => $service->id,
                    'status'        => $currentStatus,
                    'response_ms'   => $responseTime,
                    'http_code'     => $httpCode,
                    'error_message' => $errorMsg,
                    'triggered_by'  => $triggeredBy,
                    'checked_at'    => now(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            $results[] = [
                'id'          => $service->id,
                'name'        => $service->name,
                'status'      => $currentStatus,
                'response_ms' => $responseTime,
            ];
        }

        if (!empty($logsToInsert)) {
            CheckLog::insert($logsToInsert);
        }
    });

    $stats = collect($results)->countBy('status');

    return response()->json([
        'total'   => count($results),
        'online'  => $stats->get('online', 0),
        'offline' => $stats->get('offline', 0),
        'results' => $results,
    ]);
}

private function buildRequest(Pool $pool, $service)
{
    $req = $pool->timeout(5)->connectTimeout(3);

    if ($service->auth_type === 'bearer' && $service->auth_value) {
        return $req->withToken($service->auth_value);
    }

    if ($service->auth_type === 'basic' && $service->auth_value) {
        return $req->withBasicAuth(...explode(':', $service->auth_value, 2));
    }

    return $req;
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
