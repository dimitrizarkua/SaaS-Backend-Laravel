<?php

namespace App\Http\Controllers\Broadcast;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

/**
 * Class AuthController
 *
 * @package App\Http\Controllers\Broadcast
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/broadcasting/auth",
     *     tags={"Auth"},
     *     security={{"passport": {}}},
     *     summary="Authenticate the request for channel access.",
     *     description="Authenticate the request for channel access. See <a
    href=https://docs.google.com/document/d/1KlXt27Zqkf4_FW57N-zw-ZdcO2dnV7Ps0C3ksJK_7l0>Pusher notification
    document</a>.",
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function authenticate(Request $request)
    {
        return Broadcast::auth($request);
    }
}
