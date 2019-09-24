<?php

namespace App\Http\Controllers\Auth;

use App\Components\Auth\Events\ForgotPasswordRequestedEvent;
use App\Components\Auth\Interfaces\ForgotPasswordServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\SetPasswordRequest;
use App\Http\Responses\ApiOKResponse;
use App\Models\User;

/**
 * Class ForgotPasswordController
 *
 * @package App\Http\Controllers\Auth
 */
class ForgotPasswordController extends Controller
{
    /**
     * @var ForgotPasswordServiceInterface
     */
    private $service;

    /**
     * ForgotPasswordController constructor.
     *
     * @param ForgotPasswordServiceInterface $service
     */
    public function __construct(ForgotPasswordServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Post(
     *      path="/auth/password/forgot",
     *      tags={"Auth"},
     *      summary="Allows to request an email with password reset token",
     *      description="Allows to request an email with password reset token. After call to this endpoint an email will
    be sent to specified address. The email will contain password reset link in the following format:
    `https://{base_url}/reset/{reset_password_token}`. The `reset_password_token` should be used for set new password
    endpoint.
     **Note:** Even if user with given email doesn't exists the backend will always return 200 status code.
    ",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/ForgotPasswordRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
     * )
     */
    public function forgot(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->getEmail())
            ->first();

        if (null === $user) {
            return ApiOKResponse::make();
        }

        $resetPasswordUrl = $this->service->generateResetPasswordLink($user);
        event(new ForgotPasswordRequestedEvent($user, $resetPasswordUrl));

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/auth/password/reset",
     *      tags={"Auth"},
     *      summary="Allows to request an email with password reset token",
     *      description="Allows to set new password in exchange of password reset token",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/SetPasswordRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
     *      @OA\Response(
     *         response=405,
     *         description="Invalid token.",
     *      ),
     * )
     */
    public function setPassword(SetPasswordRequest $request)
    {
        $this->service->setPassword($request->token, $request->password);

        return ApiOKResponse::make();
    }
}
