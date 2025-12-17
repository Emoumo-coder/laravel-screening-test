<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    // workshops relationship
    public function workshops()
    {
        return $this->hasMany(Workshop::class);
    }
}
