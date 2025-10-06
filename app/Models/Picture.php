<?php

namespace App\Models;

use App\Enums\PictureTypeEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Picture extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'foreign_id',
        'type',
        'caption',
        'file_path',
        'mine_type',
        'alt_text',
    ];

    protected $casts = [
        'type' => PictureTypeEnum::class,
    ];

    /**
     * Get the parent model (polymorphic relationship)
     */
    public function pictureable()
    {
        return $this->morphTo('foreign_id');
    }

    public function disaster()
    {
        return $this->belongsTo(Disaster::class, 'foreign_id');
    }

    public function disasterReport()
    {
        return $this->belongsTo(DisasterReport::class, 'foreign_id');
    }

    public function disasterVictim()
    {
        return $this->belongsTo(DisasterVictim::class, 'foreign_id');
    }

    public function disasterAid()
    {
        return $this->belongsTo(DisasterAid::class, 'foreign_id');
    }
}
