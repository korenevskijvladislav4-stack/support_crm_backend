<?php

namespace App\Http\Controllers\Export;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Response;

class ExportController extends BaseController
{
    public function __invoke(Request $request, $resource)
    {
        $modelMap = [
            'users' => \App\Models\User::class,
            // и т.д.
        ];

        $modelClass = $modelMap[$resource] ?? abort(404);

        $data = $modelClass::all();

        $filename = "{$resource}_export_" . now()->format('Ymd_His') . ".csv";
        $headers = ['Content-Type' => 'text/csv'];

        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            if ($data->isNotEmpty()) {
                fputcsv($handle, array_keys($data->first()->toArray()));
                foreach ($data as $row) {
                    fputcsv($handle, $row->toArray());
                }
            }
            fclose($handle);
        };

        return Response::stream($callback, 200, $headers + [
                'Content-Disposition' => "attachment; filename={$filename}",
            ]);
    }
}
