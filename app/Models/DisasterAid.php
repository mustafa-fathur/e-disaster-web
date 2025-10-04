<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisasterAid extends Model
{
    use HasFactory;

    protected $fillable = [
        'disaster_id',
        'reported_by',
        'title',
        'description',
        'quantity',
        'unit',
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
