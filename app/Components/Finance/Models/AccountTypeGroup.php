<?php

namespace App\Components\Finance\Models;

use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Annotations as OA;

/**
 * Class AccountTypeGroup
 *
 * @property int                           $id
 * @property string                        $name
 * @property-read Collection|AccountType[] $accountTypes
 *
 * @method static Builder|AccountTypeGroup newModelQuery()
 * @method static Builder|AccountTypeGroup newQuery()
 * @method static Builder|AccountTypeGroup query()
 * @method static Builder|AccountTypeGroup whereId($value)
 * @method static Builder|AccountTypeGroup whereName($value)
 *
 * @OA\Schema(
 *     required={"id", "name"}
 * )
 *
 * @mixin \Eloquent
 */
class AccountTypeGroup extends Model
{
    use ApiRequestFillable;

    public $timestamps = false;

    protected $guarded = ['id'];

    /**
     * @OA\Property(
     *    property="id",
     *    description="Account type group identifier.",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="name",
     *    ref="#/components/schemas/AccountTypeGroups"
     * ),
     */

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accountTypes(): HasMany
    {
        return $this->hasMany(AccountType::class);
    }
}
