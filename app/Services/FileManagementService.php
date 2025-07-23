<?php

namespace App\Services;

use App\Mail\UploadNotification;
use App\Models\UploadFile;
use App\Models\UploadSession;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileManagementService
{
    public function handleUpload(array $files, ?int $expiresIn, ?string $emailToNotify, ?string $password): UploadSession
    {
        try {
            DB::beginTransaction();

            $session = UploadSession::create([
                'expires_in' => $expiresIn ?? 1,
                'email_to_notify' => $emailToNotify,
                'password' => $password ? bcrypt($password) : null,
            ]);

            foreach ($files as $file) {
                $path = $file->store("uploads/{$session->token}");

                UploadFile::create([
                    'upload_session_id' => $session->id,
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getClientMimeType(),
                ]);
            }

            if ($emailToNotify) {
                $message = (new UploadNotification($session->token))->onQueue('emails');

                Mail::to($emailToNotify)->queue($message);
            }

            DB::commit();

            return $session;

        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception('File upload failed: '.$e->getMessage(), 0, $e);
        }
    }

    public function handleDownload($password, string $token): BinaryFileResponse|JsonResponse|StreamedResponse
    {
        $session = UploadSession::where('token', $token)->firstOrFail();

        if ($session->isExpired()) {
            return response()->json(['message' => 'This file has expired.'], 410);
        }

        if ($session->password) {
            if (! $password || ! Hash::check($password, $session->password)) {
                return response()->json(['message' => 'Password required or incorrect.'], 401);
            }
        }

        $files = $session->files()->get();

        if ($files->isEmpty()) {
            return response()->json(['message' => 'No files found for this session.'], 404);
        }

        $session->increment('download_count');

        // Single file download
        if ($files->count() === 1) {
            $file = $files->first();

            if (! Storage::exists($file->path)) {
                return response()->json(['message' => 'File not found.'], 404);
            }

            return Storage::download($file->path, $file->name);
        }

        // Zip Multiple files
        $zipFileName = "uploads/{$session->token}/download.zip";
        $tmpZipPath = storage_path("app/{$zipFileName}");

        $zip = new \ZipArchive;

        if ($zip->open($tmpZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return response()->json(['message' => 'Unable to create ZIP file.'], 500);
        }

        foreach ($files as $file) {
            if (Storage::exists($file->path)) {
                $zip->addFile(storage_path('app/'.$file->path), $file->name);
            }
        }

        $zip->close();

        return response()->download($tmpZipPath, "session_{$session->token}_files.zip")->deleteFileAfterSend();
    }

    public function getStats(string $token)
    {
        $session = UploadSession::with('files')->where('token', $token)->firstOrFail();

        return [
            'token' => $session->token,
            'expires_at' => $session->expires_at,
            'file_count' => $session->files->count(),
            'download_count' => $session->download_count,
            'files' => $session->files->map(fn ($file) => [
                'name' => $file->name,
                'size' => $this->readableFileSize($file->size),
                'type' => $file->type,
            ]),
        ];
    }

    protected function readableFileSize(int $bytes, int $decimals = 2): string
    {
        $size = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).$size[$factor];
    }
}
