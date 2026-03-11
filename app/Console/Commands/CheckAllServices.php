<?php

namespace App\Console\Commands;

use App\Http\Controllers\CheckController;
use Illuminate\Console\Command;

class CheckAllServices extends Command
{
    protected $signature = 'services:check';
    protected $description = 'Ping semua service aktif';

    public function handle(CheckController $controller)
    {
        $controller->all('scheduler');
        $this->info('Check selesai: ' . now());
    }
}