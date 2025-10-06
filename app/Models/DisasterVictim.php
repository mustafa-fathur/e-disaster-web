<?php

namespace App\Models;

use App\Enums\DisasterVictimStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisasterVictim extends Model
{
    use HasFactory, HasUuids;

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
        'status' => DisasterVictimStatusEnum::class,
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

    public function pictures()
    {
        return $this->morphMany(Picture::class, 'foreign_id');
    }
}
