<?php

namespace App\Modules\Shared\Domain\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    protected static function newFactory(): SettingFactory
    {
        return SettingFactory::new();
    }
}
