<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use App\Exceptions\UnauthorizedException;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            if ($this->checkIfUserHasToken()) {
                JWTAuth::parseToken()->authenticate();
            } else {
                throw new UnauthorizedException("Authorization token not found");
            }
        } catch (TokenExpiredException $e) {
            throw new UnauthorizedException("Authorization token expired");
        } catch (JWTException $e) {
            throw new UnauthorizedException("Authorization token error");
        }
        return $next($request);
    }

    protected function checkIfUserHasToken()
    {
        return request()->headers->has('Authorization');
    }
}
