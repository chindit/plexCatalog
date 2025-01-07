<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Kyslik\ColumnSortable\Sortable;
use Ramsey\Uuid\Uuid;

/**
 * @property int $server_id
 * @property int $library_id
 * @property string $title
 * @property string $audio_codec
 * @property string $video_codec
 * @property float aspect_ratio
 * @property int $bitrate
 * @property string $container
 * @property int $duration
 * @property string $framerate
 * @property int $height
 * @property int $width
 * @property string $profile
 * @property int $resolution
 * @property string $summary
 * @property string $thumb
 * @property int $year
 * @property array $actors
 * @property array $genres
 *
 * @mixin \Eloquent
 */
class Media extends Model
{
    use HasFactory, Sortable;

    public $incrementing = false;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Authenticatable $model) {
            $model->setAttribute($model->getKeyName(), Uuid::uuid4());
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'server_id',
        'library_id',
        'title',
        'audio_codec',
        'video_codec',
        'aspect_ratio',
        'bitrate',
        'container',
        'duration',
        'framerate',
        'height',
        'width',
        'profile',
        'resolution',
        'summary',
        'thumb',
        'year',
        'user_id',
        'actors',
        'genres',
    ];

    protected $sortable = [
        'audio_codec',
        'video_codec',
        'title',
        'aspect_ratio',
        'bitrate',
        'framerate',
        'resolution',
        'container',
        'duration',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'server_id' => 'int',
        'library_id' => 'int',
        'aspect_ratio' => 'float',
        'bitrate' => 'int',
        'duration' => 'int',
        'height' => 'int',
        'width' => 'int',
        'resolution' => 'int',
        'year' => 'int',
        'actors' => 'array',
        'genres' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
