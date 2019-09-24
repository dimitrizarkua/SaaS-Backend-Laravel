<?php

namespace App\Components\Addresses\Models;

use App\Components\Jobs\Models\Job;
use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Annotations as OA;

/**
 * Class Address
 *
 * @property int         $id
 * @property string|null $contact_name
 * @property string|null $contact_phone
 * @property int|null    $suburb_id
 * @property string      $address_line_1
 * @property string|null $address_line_2
 * @method static Builder|Address whereAddressLine1($value)
 * @method static Builder|Address whereAddressLine2($value)
 * @method static Builder|Address whereContactName($value)
 * @method static Builder|Address whereContactPhone($value)
 * @method static Builder|Address whereId($value)
 * @method static Builder|Address whereName($value)
 * @method static Builder|Address whereSuburbId($value)
 * @mixin \Eloquent
 *
 * @property-read Suburb $suburb
 * @property-read string $full_address
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","address_line_1"}
 * )
 *
 */
class Address extends Model
{
    use ApiRequestFillable;

    /**
     * @OA\Property(
     *     property="id",
     *     description="Address Identifier",
     *     type="integer",
     *     example="1"
     * )
     * @OA\Property(
     *     property="contact_name",
     *     description="Contact name",
     *     type="string",
     *     example="Daniel McKenzie",
     *     nullable=true,
     * )
     * @OA\Property(
     *     property="contact_phone",
     *     description="Contact phone number",
     *     type="string",
     *     example="0413456989",
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="suburb_id",
     *     description="Suburb id",
     *     type="int",
     *     example="1",
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="address_line_1",
     *     description="Address line 1",
     *     type="string",
     *     example="143 Mason St",
     * ),
     * @OA\Property(
     *     property="address_line_2",
     *     description="Address line 2",
     *     type="string",
     *     example="143 Mason St",
     *     nullable=true,
     * ),
     */

    public $timestamps = false;

    protected $guarded = ['id'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['full_address'];

    /**
     * Define relationship with suburb table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function suburb(): BelongsTo
    {
        return $this->belongsTo(Suburb::class, 'suburb_id', 'id', 'suburbs');
    }

    /**
     * Define relationship with suburb table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(
            Job::class,
            'job_id',
            'id'
        );
    }

    /**
     * Search addresses records.
     *
     * @param array $searchData Array of filters.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function search(array $searchData = []): Builder
    {
        $query = static::query();
        if (isset($searchData['suburb_id'])) {
            $query->whereHas('suburb', function (Builder $query) use ($searchData) {
                return $query->where('id', $searchData['suburb_id']);
            });
        }
        if (isset($searchData['state_id'])) {
            $query->whereHas('suburb.state', function (Builder $query) use ($searchData) {
                return $query->where('id', $searchData['state_id']);
            });
        }
        if (isset($searchData['address_line'])) {
            $query->where(function (Builder $query) use ($searchData) {
                $value = '%' . $searchData['address_line'] . '%';
                $query->orWhere('address_line_1', 'ilike', $value)
                    ->orWhere('address_line_2', 'ilike', $value);
            });
        }
        if (isset($searchData['contact_name'])) {
            $query->where('contact_name', 'ilike', '%' . $searchData['contact_name'] . '%');
        }

        return $query;
    }

    /**
     * Get the full address as string.
     *
     * @return string
     */
    public function getFullAddressAttribute(): string
    {
        $result = $this->address_line_1;
        if (!$this->suburb) {
            return $result;
        }

        $result .= ', ' . $this->suburb->name;
        $result .= ' ' . $this->suburb->state->code;
        $result .= ' ' . $this->suburb->postcode;

        return $result;
    }
}
