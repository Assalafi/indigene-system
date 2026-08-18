<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'scope_type', 'scope_id', 'key', 'value', 'is_secret', 'version', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_secret' => 'boolean',
        ];
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function getSetting(string $key, $default = null, ?string $scopeId = null, string $scopeType = 'global'): mixed
    {
        $setting = static::where('key', $key)
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->first();

        if (! $setting) {
            return $default;
        }

        return $setting->is_secret ? null : $setting->value;
    }

    public static function setSetting(string $key, mixed $value, ?string $scopeId = null, string $scopeType = 'global'): void
    {
        $setting = static::where('key', $key)
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->first();

        if ($setting) {
            $setting->value = (string) $value;
            $setting->version = $setting->version + 1;
            $setting->updated_by = auth()->id();
            $setting->save();
        } else {
            static::create([
                'key' => $key,
                'value' => (string) $value,
                'scope_type' => $scopeType,
                'scope_id' => $scopeId,
                'updated_by' => auth()->id(),
            ]);
        }
    }
}
