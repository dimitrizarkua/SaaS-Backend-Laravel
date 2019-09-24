<?php

namespace App\Components\Messages\Models;

use App\Components\Documents\Models\Document;
use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DocumentMessage
 *
 * @package App\Components\Messages\Models
 *
 * @mixin \Eloquent
 * @property int                                            $message_id
 * @property int                                            $document_id
 * @property-read \App\Components\Messages\Models\Message   $message
 * @property-read \App\Components\Documents\Models\Document $document
 */
class DocumentMessage extends Model
{
    use HasCompositePrimaryKey;

    public $incrementing = false;
    public $timestamps   = false;

    protected $table      = 'document_message';
    protected $fillable   = ['document_id', 'message_id',];
    protected $primaryKey = ['document_id', 'message_id',];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
