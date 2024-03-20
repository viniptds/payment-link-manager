<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Settings extends Model
{
    use HasFactory;
    
    const APP_NAME = 'app_name';
    const LOGO_MAIN = 'logo_main';
    const DEFAULT_LANGUAGE = 'default_language';
    
    public $incrementing = false;
    protected $fillable = ['id', 'value', 'type', 'description', 'created_at', 'updated_at', 'updated_by'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function getFactoryValues() {
        return [
            self::APP_NAME => [
                'value' => 'MyCompanyName',
                'type' => 'text',
                'description' => ''
            ],
            self::DEFAULT_LANGUAGE => [
                'value' => 'pt-BR',
                'type' => 'text',
                'description' => ''
            ],
            self::LOGO_MAIN => [
                'value' => 'logo.png',
                'type' => 'file',
                'description' => ''
            ]
        ];
    }
}
