<?php

namespace App\Components\Auth\Services;

use App\Components\Auth\Exceptions\InvalidTokenException;
use App\Components\Auth\Interfaces\ForgotPasswordServiceInterface;
use App\Enums\UserTokenTypes;
use App\Events\PasswordChanged;
use App\Models\User;
use App\Models\UserToken;
use App\Utils\Url;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class ForgotPasswordService
 *
 * @package App\Components\Auth\Services
 */
class ForgotPasswordService implements ForgotPasswordServiceInterface
{
    /**
     * Generates reset password url.
     *
     * @param \App\Models\User $user
     *
     * @return string
     */
    public function generateResetPasswordLink(User $user): string
    {
        $token = $this->generateToken($user);

        return Url::getFullUrl('/reset/' . $token);
    }

    /**
     * Generates reset password token.
     *
     * @param \App\Models\User $user
     *
     * @return string
     */
    public function generateToken(User $user): string
    {
        $token = Str::random(64);

        $existingToken = UserToken::where([
            'user_id' => $user->id,
            'type'    => UserTokenTypes::RESET_PASSWORD,
        ])
            ->first();

        if (null === $existingToken) {
            UserToken::create([
                'user_id'    => $user->id,
                'type'       => UserTokenTypes::RESET_PASSWORD,
                'token'      => $token,
                'expires_at' => Carbon::now()->addHour(self::LINK_LIFE_TIME),
            ]);
        } else {
            $existingToken->expires_at = Carbon::now()->addHour(self::LINK_LIFE_TIME);
            $existingToken->token      = $token;
            $existingToken->save();
        }

        return $token;
    }

    /**
     * Find user by token.
     *
     * @throws \App\Components\Auth\Exceptions\InvalidTokenException
     * @return \App\Models\User
     */
    public function findUserByToken(string $token): User
    {
        $userToken = UserToken::where('token', $token)
            ->where('expires_at', '>', new Carbon())
            ->first();

        if (null === $userToken) {
            throw new InvalidTokenException('Invalid token');
        }

        return $userToken->user;
    }

    /**
     * Set new password to user by reset password token.
     *
     * @param string $resetPasswordToken
     * @param string $password
     *
     * @throws \Throwable
     * @throws \App\Components\Auth\Exceptions\InvalidTokenException
     */
    public function setPassword(string $resetPasswordToken, string $password): void
    {
        $user = $this->findUserByToken($resetPasswordToken);

        DB::transaction(function () use ($user, $password, $resetPasswordToken) {
            $user->setPassword($password);
            $user->saveOrFail();

            UserToken::where('token', $resetPasswordToken)
                ->delete();
        });

        event(new PasswordChanged($user));
    }
}
