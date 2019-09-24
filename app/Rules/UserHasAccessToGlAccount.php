<?php

namespace App\Rules;

use App\Components\Finance\Models\GLAccount;
use App\Components\Locations\Models\Location;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class UserHasAccessToGlAccount
 *
 * @package App\Rules
 */
class UserHasAccessToGlAccount implements Rule
{
    /**
     * @var User
     */
    private $user;

    /**
     * UserHasAccessToGlAccount constructor.
     *
     * @param \App\Models\User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value): bool
    {
        $glAccount   = GLAccount::with('accountingOrganization.locations')->findOrFail($value);
        $locationIds = $glAccount->accountingOrganization
            ->locations
            ->pluck('id')
            ->toArray();

        return $this->user->locations->contains(function (Location $location) use ($locationIds) {
            return in_array($location->id, $locationIds, true);
        });
    }

    /**
     * @inheritDoc
     */
    public function message(): string
    {
        return 'You are not able to perform this operation because GL account'
            . ' belongs to the location which you does not belongs to';
    }
}
