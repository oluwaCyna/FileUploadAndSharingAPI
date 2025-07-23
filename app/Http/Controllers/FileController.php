<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadFileRequest;
use App\Services\FileManagementService;
use Illuminate\Http\Request;

class FileController extends Controller
{
    protected FileManagementService $fileManager;

    public function __construct(FileManagementService $fileManagementService)
    {
        $this->fileManager = $fileManagementService;
    }

    public function store(UploadFileRequest $request)
    {
        $session = $this->fileManager->handleUpload(
            $request->file('files'),
            $request->expires_in,
            $request->email_to_notify,
            $request->password
        );

        return response()->json([
            'success' => true,
            'download_link' => route('api.download', ['token' => $session->token]),
        ]);
    }

    public function download(Request $request, string $token)
    {
        return $this->fileManager->handleDownload(
            $request->query('password'),
            $token
        );
    }

    public function stats(string $token)
    {
        return response()->json($this->fileManager->getStats($token));
    }
}
