<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttemptController;
use App\Http\Controllers\ExtraShiftController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\QualityController;
use App\Http\Controllers\QualityCriterionController;
use App\Http\Controllers\QualityCriteriaCategoryController;
use App\Http\Controllers\QualityDeductionController;
use App\Http\Controllers\QualityCallDeductionController;
use App\Http\Controllers\QualityMapController;
use App\Http\Controllers\QualityReviewController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ScheduleTypeController;
use App\Http\Controllers\ShiftRequestController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PenaltyController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'user']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// Resource routes
Route::middleware('auth:sanctum')->group(function () {
    // Users
    Route::apiResource('users', UserController::class)->middleware('permission:users,sanctum');
    Route::get('users/{user}/show', [UserController::class, 'show'])->name('users.show')->middleware('permission:users,sanctum');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('permission:users,sanctum');
    Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate')->middleware('permission:users,sanctum');
    Route::post('users/{id}/activate', [UserController::class, 'activate'])->name('users.activate')->middleware('permission:users,sanctum');
    Route::post('users/{user}/transfer-group', [UserController::class, 'transferGroup'])->name('users.transfer-group')->middleware('permission:users,sanctum');
    
    // Attempts
    Route::apiResource('attempts', AttemptController::class)->only(['index', 'show', 'destroy']);
    Route::post('attempts/{attempt}/approve', [AttemptController::class, 'approve'])->name('attempts.approve');
    
    // Teams
    Route::apiResource('teams', TeamController::class)->only(['index', 'store', 'update', 'destroy']);
    
    // Groups
    Route::apiResource('groups', GroupController::class)->only(['index', 'store', 'update', 'destroy']);
    
    // Roles
    Route::apiResource('roles', RoleController::class)->only(['index', 'store', 'update', 'destroy']);
    
    // Schedule Types
    Route::get('schedule_types', [ScheduleTypeController::class, 'index'])->name('schedule_types.index');
    
    // Schedule
    Route::apiResource('schedule', ScheduleController::class)->only(['index', 'store']);
    
    // Shift Requests (запросы дополнительных смен)
    Route::apiResource('shift-requests', ShiftRequestController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('shift-requests/{userShift}/approve', [ShiftRequestController::class, 'approve'])->name('shift-requests.approve');
    Route::post('shift-requests/{userShift}/reject', [ShiftRequestController::class, 'reject'])->name('shift-requests.reject');
    Route::post('shift-requests/create-direct', [ShiftRequestController::class, 'createDirect'])->name('shift-requests.create-direct');
    
    // Permissions
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    
    // Qualities
    Route::apiResource('qualities', QualityController::class)->only(['index', 'show']);
    
    // Quality Reviews
    Route::apiResource('quality-reviews', QualityReviewController::class)->only(['index', 'show', 'store', 'update']);
    
    // Quality Criteria
    Route::apiResource('quality-criteria', QualityCriterionController::class);
    
    // Quality Criteria Categories
    Route::apiResource('quality-criteria-categories', QualityCriteriaCategoryController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    
    // Quality Maps
    Route::apiResource('quality-maps', QualityMapController::class);
    Route::put('quality-maps/{qualityMap}/chat-ids', [QualityMapController::class, 'updateChatIds'])->name('quality-maps.update-chat-ids');
    Route::put('quality-maps/{qualityMap}/call-ids', [QualityMapController::class, 'updateCallIds'])->name('quality-maps.update-call-ids');
    
    // Quality Deductions
    Route::post('quality-deductions', [QualityDeductionController::class, 'store'])->name('quality-deductions.store');
    
    // Quality Call Deductions
    Route::post('quality-call-deductions', [QualityCallDeductionController::class, 'store'])->name('quality-call-deductions.store');
    
    // Extra Shifts
    Route::apiResource('extra-shifts', ExtraShiftController::class)->only(['index', 'store']);
    Route::post('extra-shifts/{extraShift}/approve', [ExtraShiftController::class, 'approve'])->name('extra-shifts.approve');
    Route::post('extra-shifts/{extraShift}/reject', [ExtraShiftController::class, 'reject'])->name('extra-shifts.reject');
    
    // Penalties (Штрафная таблица)
    Route::apiResource('penalties', PenaltyController::class)->only(['index', 'show', 'store', 'update']);
    Route::post('penalties/{penalty}/approve', [PenaltyController::class, 'approve'])->name('penalties.approve');
    Route::post('penalties/{penalty}/reject', [PenaltyController::class, 'reject'])->name('penalties.reject');
});

