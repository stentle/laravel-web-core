<?php


namespace Stentle\Webcore\Middleware;
use Stentle\Webcore\Business\Authentication;
use Closure;

class Session
{

    private $auth;

    public function __construct(Authentication $auth)
    {
        $this->auth=$auth;
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

        //autologin
        if(isset($_COOKIE['email']) && isset($_COOKIE['password'])){
            if(!$this->auth->check()){
                $this->auth->login($_COOKIE['email'],$_COOKIE['password']);
            }
        }else{ //se per qualche motivo la sessione esiste ma non ci sono i cookie memorizzati allora forzo un logout generale
            if($this->auth->check()){
                \Illuminate\Support\Facades\Session::flush();
            }
        }

        return $next($request);
    }

}
