<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingContent extends Model
{
    protected $table = 'landing_contents';

    protected $fillable = ['section', 'data'];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Ambil konten sebuah section; fallback ke nilai default bila belum ada di DB.
     */
    public static function section(string $key, array $default = []): array
    {
        $row = static::where('section', $key)->first();

        if (!$row) {
            return $default;
        }

        $data = $row->data ?: [];

        // Merge default agar field baru tetap terisi walau data lama belum punya
        return array_replace_recursive($default, $data);
    }

    /** Simpan / perbarui konten section. */
    public static function put(string $key, array $data): void
    {
        static::updateOrCreate(
            ['section' => $key],
            ['data' => $data],
        );
    }
}
