<?php

namespace App\Components\Contacts\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * Class AddressContact
 *
 * @package App\Components\Contacts\Models
 * @mixin \Eloquent
 *
 * @property int    $address_id
 * @property int    $contact_id
 * @property string $type
 *
 * @OA\Schema (
 *     type="object",
 *     required={"address_id","contact_id","type"}
 * )
 */
class AddressContact extends Model
{
    public $incrementing = false;
    public $timestamps   = false;

    protected $fillable   = ['type', 'address_id', 'contact_id'];
    protected $primaryKey = ['address_id', 'contact_id', 'type'];
    protected $table      = 'address_contact';
}
