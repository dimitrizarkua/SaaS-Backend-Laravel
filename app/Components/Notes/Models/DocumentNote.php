<?php

namespace App\Components\Notes\Models;

use App\Components\Documents\Models\Document;
use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DocumentNote
 *
 * @package App\Components\Notes\Models
 *
 * @mixin \Eloquent
 * @property int                                            $note_id
 * @property int                                            $document_id
 * @property-read \App\Components\Notes\Models\Note         $note
 * @property-read \App\Components\Documents\Models\Document $document
 */
class DocumentNote extends Model
{
    use HasCompositePrimaryKey;

    public $incrementing = false;
    public $timestamps   = false;

    protected $table      = 'document_note';
    protected $fillable   = ['document_id', 'note_id',];
    protected $primaryKey = ['document_id', 'note_id',];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function note()
    {
        return $this->belongsTo(Note::class);
    }
}
