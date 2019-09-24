<?php

namespace App\Components\Contacts\Models;

use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * Class ContactPersonProfile
 *
 * @package App\Components\Contacts\Models
 * @mixin \Eloquent
 *
 * @property int          $contact_id
 * @property string       $first_name
 * @property string       $last_name
 * @property string|null  $job_title
 * @property string|null  $direct_phone
 * @property string|null  $mobile_phone
 *
 * @property-read Contact $contact
 *
 * @OA\Schema (
 *     type="object",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Contact"),
 *     },
 *     required={"contact_id","first_name","last_name"}
 * )
 */
class ContactPersonProfile extends Model
{
    use ApiRequestFillable;

    protected $touches = ['contact'];

    public $incrementing = false;
    public $timestamps   = false;

    /**
     * @OA\Property(
     *     property="contact_id",
     *     type="integer",
     *     description="Contact identifier",
     *     example=1
     * ),
     * @OA\Property(
     *     property="first_name",
     *     type="string",
     *     description="First name",
     *     example="John"
     * ),
     * @OA\Property(
     *     property="last_name",
     *     type="string",
     *     description="Last name",
     *     example="Smith"
     * ),
     * @OA\Property(
     *     property="job_title",
     *     type="string",
     *     description="Job title",
     *     example="Technician"
     * ),
     * @OA\Property(
     *     property="direct_phone",
     *     type="string",
     *     description="Direct phone",
     *     example="0398776000"
     * ),
     * @OA\Property(
     *     property="mobile_phone",
     *     type="string",
     *     description="Mobile phone",
     *     example="0398776000"
     * ),
     */

    protected $primaryKey = 'contact_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contact_id',
        'first_name',
        'last_name',
        'job_title',
        'direct_phone',
        'mobile_phone',
    ];

    /**
     * Define relationship with contacts table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    /**
     * Get person's full name
     *
     * @return string
     */
    public function getFullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
