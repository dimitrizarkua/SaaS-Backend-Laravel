<?php

namespace App\Components\Contacts\Models;

use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * Class ContactCompanyProfile
 *
 * @package App\Components\Contacts\Models
 * @mixin \Eloquent
 *
 * @property int          $contact_id
 * @property string       $legal_name
 * @property string|null  $trading_name
 * @property string       $abn
 * @property string|null  $website
 * @property int          $default_payment_terms_days
 *
 * @property-read Contact $contact
 *
 * @OA\Schema (
 *     type="object",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Contact"),
 *     },
 *     required={"contact_id","legal_name","abn","default_payment_terms_days"}
 * )
 */
class ContactCompanyProfile extends Model
{
    use ApiRequestFillable;

    protected $touches = ['contact'];

    public $incrementing = false;

    /**
     * @OA\Property(
     *     property="contact_id",
     *     type="integer",
     *     description="Contact identifier",
     *     example=1
     * ),
     * @OA\Property(
     *     property="legal_name",
     *     type="string",
     *     description="Legal name",
     *     example="Yarra Valley Water"
     * ),
     * @OA\Property(
     *     property="trading_name",
     *     type="string",
     *     description="Trading name",
     *     example="Yarra Valley Water"
     * ),
     * @OA\Property(
     *     property="abn",
     *     type="string",
     *     description="Australian Business Number",
     *     example="89 897 456 578"
     * ),
     * @OA\Property(
     *     property="website",
     *     type="string",
     *     description="Website",
     *     example="yarra-valley-water.com.au"
     * ),
     * @OA\Property(
     *     property="default_payment_terms_days",
     *     type="integer",
     *     description="Default payment terms",
     *     example=30
     * ),
     */

    protected $primaryKey = 'contact_id';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contact_id',
        'legal_name',
        'trading_name',
        'abn',
        'website',
        'default_payment_terms_days',
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
}
