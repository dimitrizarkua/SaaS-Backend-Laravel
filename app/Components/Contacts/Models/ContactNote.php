<?php

namespace App\Components\Contacts\Models;

use App\Components\Meetings\Models\Meeting;
use App\Components\Notes\Models\Note;
use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

/**
 * Class ContactNote
 *
 * @package App\Components\Contacts\Models
 * @mixin \Eloquent
 *
 * @property int         $contact_id
 * @property int         $note_id
 * @property int         $meeting_id
 * @property Carbon      $created_at
 * @property Carbon|null $deleted_at
 *
 * @OA\Schema (
 *     type="object",
 *     required={"contact_id","note_id"}
 * )
 */
class ContactNote extends Model
{
    use HasCompositePrimaryKey, SoftDeletes;

    const UPDATED_AT = null;

    public $incrementing = false;
    public $timestamps   = true;

    protected $fillable   = ['contact_id', 'note_id'];
    protected $primaryKey = ['contact_id', 'note_id'];
    protected $table      = 'contact_note';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'deleted_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'deleted_at',
    ];

    /**
     * Defines relationship with meetings table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    /**
     * Defines relationship with notes table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * Defines relationship with contacts table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
