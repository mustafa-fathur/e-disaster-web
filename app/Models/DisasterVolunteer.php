<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisasterVolunteer extends Model
{
    use HasFactory;

    protected $fillable = [
        'disaster_id',
        'user_id',
    ];

    public function disaster()
    {
        return $this->belongsTo(Disaster::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
