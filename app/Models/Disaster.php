<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'reported_by',
        'title',
        'description',
        'source',
        'types',
        'status',
        'date',
        'time',
        'location',
        'coordinate',
        'lat',
        'long',
        'magnitude',
        'depth',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i',
        'lat' => 'float',
        'long' => 'float',
        'magnitude' => 'float',
        'depth' => 'float',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function volunteers()
    {
        return $this->hasMany(DisasterVolunteer::class);
    }
}
