<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

/**
 * Контроллер для работы с правами доступа
 */
class PermissionController extends Controller
{
    /**
     * Получить список всех прав доступа
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $permissions = Permission::all()->pluck('name');
        return response()->json($permissions);
    }
}

