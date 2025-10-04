<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Picture extends Model
{
    use HasFactory;

    protected $fillable = [
        'foreign_id',
        'type',
        'caption',
        'file_path',
        'mine_type',
        'alt_text',
    ];

    public function disaster()
    {
        return $this->belongsTo(Disaster::class, 'foreign_id');
    }
}
