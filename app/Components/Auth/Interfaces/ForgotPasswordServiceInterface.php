<?php

namespace App\Components\Auth\Interfaces;

use App\Models\User;

/**
 * Interface ForgotPasswordServiceInterface
 *
 * @package App\Components\Auth\Interfaces
 */
interface ForgotPasswordServiceInterface
{
    /**
     * Link life time in hours
     */
    public const LINK_LIFE_TIME = 24;

    /**
     * Generates reset password url.
     *
     * @param \App\Models\User $user
     *
     * @return string
     */
    public function generateResetPasswordLink(User $user): string;

    /**
     * Generates reset password token.
     *
     * @param \App\Models\User $user
     *
     * @return string
     */
    public function generateToken(User $user): string;

    /**
     * Find user by token.
     *
     * @param string $token
     *
     * @throws \App\Components\Auth\Exceptions\InvalidTokenException
     * @return \App\Models\User
     */
    public function findUserByToken(string $token): User;

    /**
     * Set new password to user by reset password token.
     *
     * @param string $resetPasswordToken
     * @param string $password
     *
     * @throws \App\Components\Auth\Exceptions\InvalidTokenException
     */
    public function setPassword(string $resetPasswordToken, string $password): void;
}
