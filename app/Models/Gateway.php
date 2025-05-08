<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'status', 'photo', 'credentials'];

    public function transactions() {
        return $this->hasMany(Payment::class);
    }
}
