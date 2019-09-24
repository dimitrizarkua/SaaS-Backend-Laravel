<?php

namespace App\Components\Contacts\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OpenApi\Annotations as OA;

/**
 * Class CompanyGroup
 *
 * @package App\Components\Contacts\Models
 * @mixin \Eloquent
 *
 * @property int                       $id
 * @property string                    $name
 *
 * @property-read Collection|Contact[] $contacts
 *
 * @OA\Schema (
 *     type="object",
 *     required={"id","name"}
 * )
 */
class CompanyGroup extends Model
{
    /**
     * @OA\Property(property="id", type="integer", description="Company group identifier", example=1),
     * @OA\Property(property="name", type="string", description="Company group name", example="Crawfords"),
     */

    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Define relationship with contacts table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'company_group_contact');
    }
}
