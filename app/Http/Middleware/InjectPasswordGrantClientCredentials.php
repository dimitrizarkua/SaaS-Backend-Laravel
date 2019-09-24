<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

/**
 * Class InjectPasswordGrantClientCredentials
 *
 * @package Laravel\Passport\Http\Middleware
 */
class InjectPasswordGrantClientCredentials
{
    /**
     * The Client Repository instance.
     *
     * @var \Laravel\Passport\ClientRepository
     */
    private $clients;

    /**
     * Create a new middleware instance.
     *
     * @param \Laravel\Passport\ClientRepository $clients
     *
     * @return void
     */
    public function __construct(ClientRepository $clients)
    {
        $this->clients = $clients;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $grantType = $request->input('grant_type');

        $grantTypesForInjections = ['password', 'password_mobile', 'social', 'social_mobile', 'refresh_token'];
        if (in_array($grantType, $grantTypesForInjections)) {
            $client = Passport::client();

            /** @var Client $passwordGrantClient */
            $passwordGrantClient = $client->where('password_client', true)->firstOrFail();

            $request->request->add([
                'client_id'     => $passwordGrantClient->id,
                'client_secret' => $passwordGrantClient->secret,
            ]);
        }

        return $next($request);
    }
}
