<?php

namespace App\Models;

use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogJobs extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'status', 'server'];

    public function getStatus(): JobStatus
    {
        return JobStatus::from($this->status);
    }

    public function setStatus(JobStatus $status): static
    {
        $this->attributes['status'] = $status;
        return $this;
    }

    /**
     * Get the catalog data for the catalog job.
     */
    public function catalogData()
    {
        return $this->hasMany(CatalogData::class, 'jobId');
    }
}
