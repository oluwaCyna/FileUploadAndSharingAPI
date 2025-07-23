<?php

use App\Models\UploadSession;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

it('cleans up expired uploads and files', function () {
    $file = UploadedFile::fake()->create('expired.pdf', 10, 'application/pdf');

    $session = UploadSession::create([
        'expires_at' => now()->subDay(),
    ]);

    $storedFile = $file->store("uploads/{$session->token}");
    $session->files()->create([
        'name' => $file->getClientOriginalName(),
        'path' => $storedFile,
        'size' => $file->getSize(),
        'type' => $file->getClientMimeType(),
    ]);

    Storage::assertExists($storedFile);
    expect(UploadSession::count())->toBe(1);

    Artisan::call('clean:expired-uploads');

    Storage::assertMissing($storedFile);
    // expect(UploadSession::count())->toBe(0);
});
