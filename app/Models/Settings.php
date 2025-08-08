<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Settings extends Model
{
    use HasFactory;

    const TYPE_TEXT = 'text';
    const TYPE_FILE = 'file';
    const TYPE_BOOLEAN = 'bool';

    const DATA_TYPES = [
        self::TYPE_TEXT,
        self::TYPE_FILE,
        self::TYPE_BOOLEAN,
    ];

    const APP_NAME = 'app_name';
    const LOGO_MAIN = 'logo_main';
    const DEFAULT_LANGUAGE = 'default_language';
    const GTM_TAG = 'gtm_tag';

    public $incrementing = false;
    protected $fillable = ['id', 'value', 'type', 'description', 'created_at', 'updated_at', 'updated_by'];

    public function getValueAttribute($value)
    {
        if ($this->type == self::TYPE_FILE) {
            return Storage::disk('public')->url('assets/' . $value);
        }
        return $value;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function getFactoryValues()
    {
        return [
            self::APP_NAME => [
                'value' => 'MyCompanyName',
                'type' => Settings::TYPE_TEXT,
                'description' => ''
            ],
            self::DEFAULT_LANGUAGE => [
                'value' => 'pt-BR',
                'type' => Settings::TYPE_TEXT,
                'description' => ''
            ],
            self::LOGO_MAIN => [
                'value' => 'logo.png',
                'type' => Settings::TYPE_FILE,
                'description' => ''
            ],
            self::GTM_TAG => [
                'value' => '',
                'type' => Settings::TYPE_TEXT,
                'description' => ''
            ]
        ];

        $logo = url('logo.png');

        Storage::disk('local')->put('assets/logo.png', file_get_contents($logo));
    }
}
