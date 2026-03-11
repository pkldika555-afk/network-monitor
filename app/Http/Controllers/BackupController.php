<?php

namespace App\Http\Controllers;

use App\Models\Services;
use App\Models\User;
use App\Models\CheckLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    public function index()
    {
        $services = Services::latest()->get();
        $categories = Services::selectRaw('category, count(*) as total, SUM(status = "offline") as offline_count')
            ->groupBy('category')
            ->orderBy('category')
            ->get();
        $stats = [
            'services' => Services::count(),
            'users' => User::count(),
            'logs' => CheckLog::count(),
            'last_backup' => session('last_backup'),
        ];
        return view('backup.index', compact('stats', 'services', 'categories'));
    }

    public function backup()
    {
        $data = [
            'meta' => [
                'app' => 'NetMonitor',
                'version' => '1.0',
                'created_at' => now()->toISOString(),
                'total' => [
                    'services' => Services::count(),
                    'users' => User::count(),
                    'logs' => CheckLog::count(),
                ],
            ],
            'users' => User::all()->makeVisible(['password', 'remember_token'])->toArray(),
            'services' => Services::all()->toArray(),
            'logs' => CheckLog::all()->toArray(),
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'netmonitor-backup-' . now()->format('Ymd-His') . '.json';

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:json|max:20480',
        ]);

        $result = $this->processRestore($request->file('backup_file'));

        if ($result['error']) {
            return back()->withErrors(['backup_file' => $result['error']]);
        }

        return back()->with('success', $result['message']);
    }

    private function processRestore($file): array
    {
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['meta'])) {
            return ['error' => 'File backup tidak valid atau rusak.', 'message' => null];
        }

        foreach (['users', 'services', 'logs'] as $key) {
            if (!array_key_exists($key, $data)) {
                return ['error' => "File backup tidak lengkap: '{$key}' tidak ditemukan.", 'message' => null];
            }
        }

        $clean = function (array $row): array {
            foreach (['created_at', 'updated_at', 'last_checked_at', 'assigned_at', 'checked_at'] as $col) {
                if (!array_key_exists($col, $row))
                    continue;
                $val = $row[$col];
                if ($val === null || $val === '' || $val === 'null') {
                    $row[$col] = null;
                    continue;
                }
                try {
                    $row[$col] = \Carbon\Carbon::parse($val)->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    $row[$col] = null;
                }
            }
            return $row;
        };

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            DB::beginTransaction();

            CheckLog::query()->delete();
            Services::query()->delete();
            User::query()->delete();

            foreach ($data['users'] as $row) {
                if (empty($row['password'])) {
                    $row['password'] = bcrypt('password123');
                }
                User::insert($clean($row));
            }
            foreach ($data['services'] as $row) {
                Services::insert($clean($row));
            }
            foreach ($data['logs'] as $row) {
                CheckLog::insert($clean($row));
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            return ['error' => 'Restore gagal: ' . $e->getMessage(), 'message' => null];
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        foreach ([
            ['users', 'id'],
            ['services', 'id'],
            ['check_logs', 'id'],
        ] as [$tbl, $pk]) {
            $max = DB::table($tbl)->max($pk) ?? 0;
            DB::statement("ALTER TABLE `{$tbl}` AUTO_INCREMENT = " . ($max + 1));
        }

        $message = "Restore berhasil! " .
            count($data['users']) . " users, " .
            count($data['services']) . " services, " .
            count($data['logs']) . " logs dipulihkan.";

        return ['error' => null, 'message' => $message];
    }
    public function restoreAwalForm()
    {
        return view('backup.restore-awal');
    }

    public function restoreAwal(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:json|max:20480',
        ]);

        $result = $this->processRestore($request->file('backup_file'));

        if ($result['error']) {
            return back()->withErrors(['backup_file' => $result['error']]);
        }

        return redirect('/restore-awal')->with('success', 'Restore berhasil! Silakan login dengan akun dari backup.');
    }
}