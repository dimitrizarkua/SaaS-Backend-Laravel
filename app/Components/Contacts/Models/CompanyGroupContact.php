<?php

namespace App\Components\Contacts\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * Class CompanyGroupContact
 *
 * @package App\Components\Contacts\Models
 * @mixin \Eloquent
 *
 * @property int $company_group_id
 * @property int $contact_id
 *
 * @OA\Schema (
 *     type="object",
 *     required={"company_group_id","contact_id"}
 * )
 */
class CompanyGroupContact extends Model
{
    public $incrementing = false;
    public $timestamps   = false;

    protected $fillable   = ['company_group_id', 'contact_id'];
    protected $primaryKey = ['company_group_id', 'contact_id'];
    protected $table      = 'company_group_contact';
}
