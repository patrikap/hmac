<?php
declare(strict_types = 1);


namespace Patrikap\Hmac\Middleware;


use Closure;
use Illuminate\Http\Request;
use Patrikap\Hmac\Services\HmacService;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Class AuthenticateWithHmac
 * @package Patrikap\Hmac\Middleware
 *
 * EN: Middleware for check HMAC request signature
 * RU: Посредник для проверки подписи запроса
 *
 * @date 08.05.2020 22:11
 * @author Patrikap
 */
class AuthenticateWithHmac
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws BindingResolutionException
     */
    public function handle($request, Closure $next)
    {
        $hmac = app()->make(HmacService::class);
        if ($hmac->checkSignature($request)) {

            return $next($request);
        }

        return abort(403, 'Access denied');
    }
}
