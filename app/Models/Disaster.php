<?php

namespace App\Models;

use App\Enums\DisasterTypeEnum;
use App\Enums\DisasterStatusEnum;
use App\Enums\DisasterSourceEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disaster extends Model
{
    use HasFactory, HasUuids;

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
        'source' => DisasterSourceEnum::class,
        'types' => DisasterTypeEnum::class,
        'status' => DisasterStatusEnum::class,
        'date' => 'date',
        'time' => 'datetime:H:i:s',
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

    public function reports()
    {
        return $this->hasMany(DisasterReport::class);
    }

    public function victims()
    {
        return $this->hasMany(DisasterVictim::class);
    }

    public function aids()
    {
        return $this->hasMany(DisasterAid::class);
    }

    public function pictures()
    {
        return $this->morphMany(Picture::class, 'foreign_id');
    }
}
