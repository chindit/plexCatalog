<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogData extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'jobId', 'data'];
    protected $casts = ['id' => 'integer', 'jobId' => 'integer'];

    public function catalogJob()
    {
        return $this->belongsTo(CatalogJobs::class, 'jobId');
    }
}
