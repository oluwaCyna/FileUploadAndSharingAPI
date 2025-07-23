<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UploadSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'email_to_notify',
        'expires_in',
        'expires_at',
        'download_count',
        'password',
    ];

    protected static function booted()
    {
        static::creating(function ($session) {
            $session->token = Str::uuid();
            $session->expires_at = Carbon::now()->addDays($session->expires_in ?? 1);
        });
    }

    public function files()
    {
        return $this->hasMany(UploadFile::class);
    }

    public function isExpired(): bool
    {
        return Carbon::parse($this->expires_at)->isPast();
    }
}
