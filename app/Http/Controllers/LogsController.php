<?php

namespace App\Http\Controllers;

use App\Services\Logs\LogsServices;

class LogsController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $path = env('PUBLIC_PATH', base_path('public')) . '/test_logs/access_log';
        $file = fopen($path, 'r');
        return $file ?
            response()->json((new LogsServices($file))->make()) :
            response()->json('Файл не найден', 400);
    }
}
