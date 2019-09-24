<?php

namespace App\Components\Contacts\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * Class ContactTag
 *
 * @package App\Components\Contacts\Models
 * @mixin \Eloquent
 *
 * @property int $tag_id
 * @property int $contact_id
 *
 * @OA\Schema (
 *     type="object",
 *     required={"tag_id","contact_id"}
 * )
 */
class ContactTag extends Model
{
    public $incrementing = false;
    public $timestamps   = false;

    protected $fillable   = ['tag_id', 'contact_id'];
    protected $primaryKey = ['tag_id', 'contact_id'];
    protected $table      = 'contact_tag';
}
