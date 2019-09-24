<?php

namespace App\Components\UsageAndActuals\Models;

use App\Components\Notes\Models\Note;
use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class EquipmentNote
 *
 * @package App\Components\UsageAndActuals\Models
 *
 * @property int            $equipment_id
 * @property int            $note_id
 * @property-read Equipment $equipment
 * @property-read Note      $note
 * @mixin \Eloquent
 */
class EquipmentNote extends Model
{
    use HasCompositePrimaryKey;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'equipment_note';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['equipment_id', 'note_id'];


    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['equipment_id', 'note_id'];

    /**
     * Relationship with equipment table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Relationship with notes table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }
}
