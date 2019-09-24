<?php

namespace App\Components\Contacts\Models;

use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * Class ContactCategory
 *
 * @package App\Components\Contacts\Models
 * @mixin \Eloquent
 *
 * @property int                       $id
 * @property string                    $name
 * @property string                    $type
 *
 * @property-read Collection|Contact[] $contacts
 *
 * @OA\Schema (
 *     type="object",
 *     required={"id","name"}
 * )
 */
class ContactCategory extends Model
{
    use ApiRequestFillable;

    /**
     * @OA\Property(property="id", type="integer", description="Contact category identifier", example=1),
     * @OA\Property(property="name", type="string", description="Contact category name", example="Customer"),
     * @OA\Property(property="type", ref="#/components/schemas/ContactCategoryTypes"),
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
     * Determines if the contact is customer.
     *
     * @return bool
     */
    public function isCustomer(): bool
    {
        return ContactCategoryTypes::CUSTOMER === strtolower($this->type);
    }

    /**
     * Determines if the contact is insurer.
     *
     * @return bool
     */
    public function isInsurer(): bool
    {
        return ContactCategoryTypes::INSURER === strtolower($this->type);
    }

    /**
     * Determines if the contact is company location.
     *
     * @return bool
     */
    public function isCompanyLocation(): bool
    {
        return ContactCategoryTypes::COMPANY_LOCATION === strtolower($this->type);
    }

    /**
     * Define relationship with contacts table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class, 'contact_category_id');
    }
}
