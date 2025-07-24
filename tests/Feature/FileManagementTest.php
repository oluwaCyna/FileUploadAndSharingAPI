<?php

use App\Mail\UploadNotification;
use App\Models\UploadSession;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    if (Str::contains(test()->name(), 'downloads_multiple_files_as_a_zip')) {
        Storage::disk('local');
    } else {
        Storage::fake('local');
    }
    Mail::fake();
    Queue::fake();
});

afterEach(function () {
    if (Str::contains(test()->name(), 'downloads_multiple_files_as_a_zip')) {
        Storage::disk('local')->deleteDirectory('uploads');
    }
});

it('uploads a single file successfully', function () {
    $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

    $response = $this->postJson('/api/upload', [
        'files' => [$file],
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'download_link']);

    $this->assertDatabaseCount('upload_sessions', 1);
    $this->assertDatabaseCount('upload_files', 1);

    Storage::assertExists(UploadSession::first()->files->first()->path);
});

it('uploads multiple files successfully', function () {
    $files = [
        UploadedFile::fake()->image('file1.jpg'),
        UploadedFile::fake()->create('file2.pdf', 50, 'application/pdf'),
    ];

    $response = $this->postJson('/api/upload', [
        'files' => $files,
        'expires_in' => 2,
        'email_to_notify' => 'user@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'download_link']);

    expect(UploadSession::first()->files()->count())->toBe(2);
    Mail::assertQueued(UploadNotification::class);
});

it('downloads a single file successfully', function () {
    $file = UploadedFile::fake()->create('file1.pdf', 20, 'application/pdf');

    $uploadResponse = $this->postJson('/api/upload', [
        'files' => [$file],
    ]);

    $uploadResponse->assertStatus(200);
    $downloadLink = $uploadResponse->json('download_link');

    $response = $this->get($downloadLink);
    $response->assertStatus(200);
    $response->assertHeader('content-disposition');

    $session = UploadSession::first();
    expect($session->download_count)->toBe(1);
});

it('downloads multiple files as a zip', function () {
    $files = [
        UploadedFile::fake()->create('file1.pdf', 20, 'application/pdf'),
        UploadedFile::fake()->create('file2.pdf', 30, 'application/pdf'),
    ];

    $uploadResponse = $this->postJson('/api/upload', [
        'files' => $files,
    ]);

    $uploadResponse->assertStatus(200);
    $downloadLink = $uploadResponse->json('download_link');

    $response = $this->get($downloadLink);

    $response->assertStatus(200);
    $response->assertHeader('content-disposition');
    $response->assertHeader('content-type', 'application/zip');

    $session = UploadSession::first();
    expect($session->download_count)->toBe(1);
});

it('prevents download without correct password', function () {
    $file = UploadedFile::fake()->create('secure.pdf', 10, 'application/pdf');

    $uploadResponse = $this->postJson('/api/upload', [
        'files' => [$file],
        'password' => 'secret123',
    ]);

    $uploadResponse->assertStatus(200);
    $downloadLink = $uploadResponse->json('download_link');

    $response = $this->get($downloadLink);
    $response->assertStatus(401);

    $responseWithPassword = $this->get($downloadLink.'?password=secret123');
    $responseWithPassword->assertStatus(200);
});

it('returns stats for a session', function () {
    $file = UploadedFile::fake()->create('statfile.pdf', 15, 'application/pdf');

    $uploadResponse = $this->postJson('/api/upload', [
        'files' => [$file],
    ]);

    $token = UploadSession::first()->token;
    $response = $this->getJson("/api/uploads/stats/{$token}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'token',
            'expires_at',
            'file_count',
            'download_count',
            'files' => [['name', 'size', 'type']],
        ]);
});
