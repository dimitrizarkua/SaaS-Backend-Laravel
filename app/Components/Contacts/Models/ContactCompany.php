<?php

namespace App\Components\Contacts\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * Class ContactCompany
 *
 * @package App\Components\Contacts\Models
 * @mixin \Eloquent
 *
 * @property int $contact_id
 * @property int $company_id
 *
 * @OA\Schema (
 *     type="object",
 *     required={"contact_id","company_id"}
 * )
 */
class ContactCompany extends Model
{
    public $incrementing = false;
    public $timestamps   = false;

    protected $fillable   = ['contact_id', 'company_id'];
    protected $primaryKey = ['contact_id', 'company_id'];
    protected $table      = 'contact_company';
}
