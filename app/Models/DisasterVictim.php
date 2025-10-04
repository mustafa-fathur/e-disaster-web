<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisasterVictim extends Model
{
    use HasFactory;

    protected $fillable = [
        'disaster_id',
        'reported_by',
        'nik',
        'name',
        'date_of_birth',
        'description',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function disaster()
    {
        return $this->belongsTo(Disaster::class);
    }

    public function reporter()
    {
        return $this->belongsTo(DisasterVolunteer::class, 'reported_by');
    }
}
