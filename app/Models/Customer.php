<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;
use App\Traits\UUID;

class Customer extends Model
{
    use HasFactory, UUID, BelongsToCompany;

    protected $fillable = [
        'id', 'name', 'email', 'document', 'cpf', 'company_id'
    ];
}
