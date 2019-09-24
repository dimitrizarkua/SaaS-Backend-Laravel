<?php

namespace App\Components\Documents\Models;

use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Jobs\Models\Job;
use App\Components\Messages\Models\Message;
use App\Components\Notes\Models\Note;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Class Document
 *
 * @package App\Components\Documents\Models
 *
 * @mixin \Eloquent
 * @property int                             $id
 * @property string                          $storage_uid
 * @property string                          $file_name
 * @property integer                         $file_size
 * @property string                          $file_hash
 * @property string|null                     $mime_type
 * @property \Illuminate\Support\Carbon      $created_at
 * @property \Illuminate\Support\Carbon      $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Collection|Note[]          $notes
 * @property-read Collection|Message[]       $messages
 * @property-read Collection|Document[]      $documents
 * @property-read Collection|PurchaseOrder[] $purchaseOrders
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","file_name","file_size", "created_at", "updated_at"}
 * )
 */
class Document extends Model
{
    use SoftDeletes;

    /**
     * @OA\Property(property="id", type="integer", example=1)
     * @OA\Property(
     *     property="file_name",
     *     description="File name (including extension)",
     *     type="string",
     *     example="document.pdf"
     * )
     * @OA\Property(
     *     property="file_size",
     *     description="File size in bytes",
     *     type="integer",
     *     example=573187
     * )
     * @OA\Property(
     *     property="mime_type",
     *     description="Mime type",
     *     type="string",
     *     example="application/pdf"
     * )
     * @OA\Property(property="created_at", type="string", format="date-time")
     * @OA\Property(property="updated_at", type="string", format="date-time")
     */

    public $timestamps = true;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'storage_uid',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'storage_uid',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'deleted_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Notes to which this document was attached.
     *
     * @return BelongsToMany
     */
    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'document_note');
    }

    /**
     * Messages to which this document was attached.
     */
    public function messages()
    {
        return $this->belongsToMany(
            Message::class,
            'document_message',
            'document_id',
            'message_id'
        );
    }

    /**
     * Jobs associated with this document.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function jobs(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Job::class,
                'job_documents',
                'document_id',
                'job_id'
            )
            ->withPivot(['creator_id', 'type', 'description', 'created_at', 'updated_at']);
    }

    /**
     * Relationship with purchase orders table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'document_id');
    }
}
