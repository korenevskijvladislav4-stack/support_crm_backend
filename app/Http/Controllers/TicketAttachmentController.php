<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiController;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TicketAttachmentController extends ApiController
{
    public function store(Request $request, Ticket $ticket): JsonResponse
    {
        \Log::info('=== FILE UPLOAD STARTED ===');
        \Log::info('Request data:', $request->all());
        \Log::info('Files in request:', $request->allFiles());

        if (!$request->hasFile('file')) {
            \Log::error('No file in request');
            return $this->error('File is required', 422, ['file' => ['The file field is required.']]);
        }

        $file = $request->file('file');

        \Log::info('File details:', [
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'isValid' => $file->isValid(),
            'error' => $file->getError()
        ]);

        if (!$file->isValid()) {
            \Log::error('File is not valid', ['error' => $file->getError()]);
            return $this->error('Invalid file', 422, ['file' => ['The file is not valid.']]);
        }

        if ($file->getSize() > 1024 * 1024 * 10) {
            return $this->error('File too large', 422, ['file' => ['The file may not be greater than 10 megabytes.']]);
        }

        try {
            return DB::transaction(function () use ($file, $ticket) {
                // Создаем путь
                $directory = 'ticket-attachments/' . date('Y/m');
                $path = $file->store($directory);

                \Log::info('File storage details:', [
                    'directory' => $directory,
                    'path' => $path,
                    'full_path' => storage_path('app/' . $path),
                    'file_exists' => \Storage::exists($path)
                ]);

                // Проверяем что файл сохранился
                if (!\Storage::exists($path)) {
                    \Log::error('File was not saved to storage', ['path' => $path]);
                    throw new \Exception('File was not saved to storage');
                }

                $fileInfo = [
                    'ticket_id' => $ticket->id,
                    'user_id' => auth()->id(),
                    'filename' => basename($path),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'path' => $path,
                    'size' => $file->getSize(),
                ];

                \Log::info('Creating attachment record:', $fileInfo);

                $attachment = TicketAttachment::create($fileInfo);

                TicketActivity::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => auth()->id(),
                    'type' => 'attachment_added',
                    'description' => 'Добавлен файл: ' . $file->getClientOriginalName(),
                ]);

                \Log::info('=== FILE UPLOAD SUCCESS ===');

                return $this->success($attachment->load('user'), 'File uploaded successfully', 201);
            });
        } catch (\Exception $e) {
            \Log::error('File upload failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return $this->error('File upload failed: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(TicketAttachment $attachment): JsonResponse
    {
        Storage::delete($attachment->path);
        $attachment->delete();

        return $this->success(null, 'File deleted successfully');
    }

    public function download(TicketAttachment $attachment): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filePath = Storage::path($attachment->path);

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->download($filePath, $attachment->original_name);
    }

    public function preview(TicketAttachment $attachment)
    {
        \Log::info('Preview attempt:', [
            'attachment_id' => $attachment->id,
            'path' => $attachment->path,
            'mime_type' => $attachment->mime_type,
            'storage_path' => Storage::path($attachment->path),
            'file_exists' => Storage::exists($attachment->path)
        ]);

        // Используем Storage для получения правильного пути
        if (!Storage::exists($attachment->path)) {
            \Log::error('File not found in storage:', ['path' => $attachment->path]);
            abort(404, 'File not found');
        }

        $filePath = Storage::path($attachment->path);

        // Для изображений показываем прямо в браузере
        if (str_starts_with($attachment->mime_type, 'image/')) {
            return response()->file($filePath, [
                'Content-Type' => $attachment->mime_type,
                'Content-Disposition' => 'inline; filename="' . $attachment->original_name . '"'
            ]);
        }

        // Для PDF тоже можно показать в браузере
        if ($attachment->mime_type === 'application/pdf') {
            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $attachment->original_name . '"'
            ]);
        }

        // Для текстовых файлов
        if (in_array($attachment->mime_type, ['text/plain', 'text/html', 'text/css', 'application/json'])) {
            return response()->file($filePath, [
                'Content-Type' => $attachment->mime_type,
                'Content-Disposition' => 'inline; filename="' . $attachment->original_name . '"'
            ]);
        }
        // Для остальных файлов - скачивание
        return response()->download($filePath, $attachment->original_name);
    }
}
