<?php

namespace Stentle\LaravelWebcore\Middleware;

use Stentle\LaravelWebcore\Contracts\Authentication;
use Closure;

/**
 * E' un middleware che consente di verificare se l'utente è autorizzato all'accesso
 * Class Permission
 * @package App\Http\Middleware
 */
class Permission
{
    /**
     * The Authentication implementation.
     *
     * @var Authentication
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Authentication $auth
     * @return void
     */
    public function __construct(Authentication $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (!$this->auth->check()) {
            return redirect()->guest('account');
        }

        return $next($request);
    }
}
