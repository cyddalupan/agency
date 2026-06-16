<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusTransition extends Model
{
    protected $fillable = ['from_code', 'to_code', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
