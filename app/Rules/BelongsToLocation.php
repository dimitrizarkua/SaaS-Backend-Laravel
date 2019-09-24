<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class BelongsToLocation
 *
 * @package App\Rules
 */
class BelongsToLocation implements Rule
{
    /**
     * @var User
     */
    private $user;

    /**
     * BelongsToLocation constructor.
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
    public function passes($attribute, $value)
    {
        return $this->user
            ->locations
            ->contains('id', $value);
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return 'You are not able to perform this operation in the location you does not belongs to';
    }
}
