<?php

namespace App\Console\Commands;

use App\Models\UploadSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanExpiredUploads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:expired-uploads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired uploads and their files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredSessions = UploadSession::where('expires_at', '<', now())->get();

        foreach ($expiredSessions as $session) {
            foreach ($session->files as $file) {
                if (Storage::exists($file->path)) {
                    Storage::delete($file->path);
                }
            }

            $session->delete();
        }

        $this->info('Expired uploads cleaned successfully.');
    }
}
