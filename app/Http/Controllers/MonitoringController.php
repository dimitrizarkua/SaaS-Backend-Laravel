<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiOKResponse;

/**
 * Class MonitoringController
 *
 * @package App\Http\Controllers
 */
class MonitoringController extends Controller
{
    /**
     * Returns current environment.
     *
     * @return \App\Http\Responses\ApiResponse
     */
    public function getEnv()
    {
        return new ApiOKResponse(\App::environment());
    }
}
