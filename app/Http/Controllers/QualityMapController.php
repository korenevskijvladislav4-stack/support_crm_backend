<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Filters\QualityMapFilter;
use App\Http\Requests\QualityMap\FilterRequest;
use App\Models\QualityCriteria;
use App\Models\QualityDeduction;
use App\Models\QualityCallDeduction;
use App\Models\QualityMap;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QualityMapController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function updateChatIds(Request $request, QualityMap $qualityMap): JsonResponse
    {
        $request->validate([
            'chat_ids' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $oldChatIds = $qualityMap->chat_ids;
            $newChatIds = $request->chat_ids;

            // Обновляем chat_id в существующих записях снятий
            foreach ($newChatIds as $index => $newChatId) {
                if ($newChatId !== ($oldChatIds[$index] ?? '')) {
                    // Обновляем все deduction записи для этого индекса чата
                    QualityDeduction::where('quality_map_id', $qualityMap->id)
                        ->where('chat_id', $oldChatIds[$index] ?? '')
                        ->update(['chat_id' => $newChatId]);
                }
            }

            $qualityMap->update(['chat_ids' => $newChatIds]);
            
            // Пересчитываем общий балл после обновления ID чатов
            $qualityMap->recalculateTotalScore();
            $qualityMap->refresh();
            
            DB::commit();

            return response()->json($qualityMap->load('deductions.criterion'));

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Ошибка при обновлении ID чатов',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(FilterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $filter = app()->make(QualityMapFilter::class, ['queryParams' => array_filter($data)]);
        
        $query = QualityMap::with(['user', 'team', 'checker', 'deductions', 'callDeductions']);
        
        // Применяем фильтры
        $query->filter($filter);

        // Сортировка
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Загружаем все данные для правильной фильтрации по статусу
        // (статус определяется на основе проверки чатов)
        $allQualityMaps = $query->get();
        $perPage = $request->get('per_page', 15);
        $currentPage = $request->get('page', 1);

        // Форматируем данные для ответа
        $formattedMaps = $allQualityMaps->map(function ($map) {
            // Получаем все chat_ids (включая пустые)
            $allChatIds = $map->chat_ids ?? [];
            $totalChatsCount = count($allChatIds);
            
            // Получаем заполненные чаты (непустые chat_ids)
            $filledChats = array_filter($allChatIds, function($chatId) {
                return !empty($chatId) && trim($chatId) !== '';
            });
            $filledChatsCount = count($filledChats);

            // Получаем все call_ids (включая пустые)
            $allCallIds = $map->call_ids ?? [];
            $totalCallsCount = $map->calls_count ?? 0;
            
            // Получаем заполненные звонки (непустые call_ids)
            $filledCalls = array_filter($allCallIds, function($callId) {
                return !empty($callId) && trim($callId) !== '';
            });
            $filledCallsCount = count($filledCalls);

            // Определяем статус: проверка завершена, если все chat_ids и call_ids заполнены
            $isCompleted = $this->isQualityMapCompleted($map, $allChatIds, $totalChatsCount, $allCallIds, $totalCallsCount);

            $formatted = [
                'id' => $map->id,
                'user_id' => $map->user_id,
                'user_name' => $map->user->name ." ". $map->user->surname,
                'team_id' => $map->team_id,
                'team_name' => $map->team->name,
                'checker_id' => $map->checker_id,
                'checker_name' => $map->checker->name . ' ' . $map->checker->surname,
                'start_date' => $map->start_date,
                'end_date' => $map->end_date,
                'created_at' => $map->created_at,
                'updated_at' => $map->updated_at,
                'status' => $isCompleted ? 'completed' : 'active',
                'total_chats' => $totalChatsCount, // Общее количество созданных чатов
                'checked_chats' => $filledChatsCount, // Количество проверенных чатов
                'total_calls' => $totalCallsCount, // Общее количество созданных звонков
                'checked_calls' => $filledCallsCount, // Количество проверенных звонков
                'total_score' => $map->total_score ?? 0, // Общий балл из БД
            ];

            return $formatted;
        });

        // Фильтрация по статусу после определения статуса для каждой карты
        $status = $request->input('status');
        if (!empty($status) && $status !== 'all') {
            $formattedMaps = $formattedMaps->filter(function ($map) use ($status) {
                return $map['status'] === $status;
            });
        }

        // Применяем пагинацию после фильтрации
        $total = $formattedMaps->count();
        $lastPage = (int) ceil($total / $perPage);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedMaps = $formattedMaps->slice($offset, $perPage)->values();

        return response()->json([
            'data' => $paginatedMaps,
            'meta' => [
                'current_page' => (int) $currentPage,
                'last_page' => $lastPage,
                'per_page' => (int) $perPage,
                'total' => $total,
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'team_id' => 'required|exists:teams,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'chat_count' => 'required|integer|min:1|max:50',
            'calls_count' => 'nullable|integer|min:0|max:50',
        ]);

        DB::beginTransaction();

        try {
            $callsCount = $validated['calls_count'] ?? 0;
            
            $qualityMap = QualityMap::create([
                'user_id' => $validated['user_id'],
                'checker_id' => auth()->id(),
                'team_id' => $validated['team_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'chat_ids' => array_fill(0, $validated['chat_count'], ''),
                'call_ids' => $callsCount > 0 ? array_fill(0, $callsCount, '') : [],
                'calls_count' => $callsCount,
            ]);

            // ИСПРАВЛЕНИЕ: Получаем только критерии для конкретной команды
            $criteria = QualityCriteria::where('is_active', true)
                ->where(function($query) use ($validated) {
                    // Глобальные критерии ИЛИ критерии привязанные к этой команде
                    $query->where('is_global', true)
                        ->orWhereHas('teams', function($q) use ($validated) {
                            $q->where('teams.id', $validated['team_id']);
                        });
                })
                ->get();

            // Создаем начальные записи снятий для чатов
            foreach ($criteria as $criterion) {
                foreach (range(0, $validated['chat_count'] - 1) as $chatIndex) {
                    QualityDeduction::create([
                        'quality_map_id' => $qualityMap->id,
                        'criteria_id' => $criterion->id,
                        'chat_id' => 'Чат'. " $chatIndex",
                        'deduction' => 0,
                        'comment' => null,
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            // Создаем начальные записи снятий для звонков
            if ($callsCount > 0) {
                foreach ($criteria as $criterion) {
                    foreach (range(0, $callsCount - 1) as $callIndex) {
                        QualityCallDeduction::create([
                            'quality_map_id' => $qualityMap->id,
                            'criteria_id' => $criterion->id,
                            'call_id' => 'Звонок'. " $callIndex",
                            'deduction' => 0,
                            'comment' => null,
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
            }

            // Пересчитываем общий балл (будет 0, так как все снятия = 0)
            $qualityMap->recalculateTotalScore();
            $qualityMap->refresh();

            DB::commit();

            return response()->json([
                'message' => 'Карта качества создана',
                'data' => $qualityMap->load('user', 'team', 'deductions.criterion', 'callDeductions.criterion')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Ошибка при создании карты качества',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(QualityMap $qualityMap): JsonResponse
    {
        return response()->json(
            $qualityMap->load([
                'user',
                'team',
                'checker',
                'deductions.criterion',
                'deductions.createdBy',
                'callDeductions.criterion',
                'callDeductions.createdBy'
            ])
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, QualityMap $qualityMap): JsonResponse
    {
        // Логика обновления если понадобится
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QualityMap $qualityMap): JsonResponse
    {
        $qualityMap->delete();

        return response()->json([
            'message' => 'Карта качества удалена'
        ]);
    }

    /**
     * Обновить ID звонков
     */
    public function updateCallIds(Request $request, QualityMap $qualityMap): JsonResponse
    {
        $request->validate([
            'call_ids' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $oldCallIds = $qualityMap->call_ids ?? [];
            $newCallIds = $request->call_ids;

            // Обновляем call_id в существующих записях снятий
            foreach ($newCallIds as $index => $newCallId) {
                if ($newCallId !== ($oldCallIds[$index] ?? '')) {
                    // Обновляем все call deduction записи для этого индекса звонка
                    QualityCallDeduction::where('quality_map_id', $qualityMap->id)
                        ->where('call_id', $oldCallIds[$index] ?? '')
                        ->update(['call_id' => $newCallId]);
                }
            }

            $qualityMap->update(['call_ids' => $newCallIds]);
            
            // Пересчитываем общий балл после обновления ID звонков
            $qualityMap->recalculateTotalScore();
            $qualityMap->refresh();
            
            DB::commit();

            return response()->json($qualityMap->load('callDeductions.criterion'));

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Ошибка при обновлении ID звонков',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Проверить, завершена ли проверка карты качества
     * Проверка считается завершенной, если все chat_ids и call_ids заполнены (не пустые)
     *
     * @param QualityMap $qualityMap
     * @param array $allChatIds Массив всех chat_ids (включая пустые)
     * @param int $totalChatsCount Общее количество чатов, созданных при создании карты
     * @param array $allCallIds Массив всех call_ids (включая пустые)
     * @param int $totalCallsCount Общее количество звонков, созданных при создании карты
     * @return bool
     */
    private function isQualityMapCompleted(QualityMap $qualityMap, array $allChatIds, int $totalChatsCount, array $allCallIds, int $totalCallsCount): bool
    {
        // Если нет чатов и звонков вообще, проверка не может быть завершена
        if ($totalChatsCount === 0 && $totalCallsCount === 0) {
            return false;
        }

        // Проверяем, что все chat_ids заполнены (не пустые строки)
        foreach ($allChatIds as $chatId) {
            // Если хотя бы один chat_id пустой, проверка не завершена
            if (empty($chatId) || trim($chatId) === '') {
                return false;
            }
        }

        // Проверяем, что все call_ids заполнены (не пустые строки)
        foreach ($allCallIds as $callId) {
            // Если хотя бы один call_id пустой, проверка не завершена
            if (empty($callId) || trim($callId) === '') {
                return false;
            }
        }

        // Все chat_ids и call_ids заполнены - проверка завершена
        return true;
    }
}
