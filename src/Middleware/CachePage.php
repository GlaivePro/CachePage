<?php

namespace GlaivePro\CachePage\Middleware;

use Closure;
use Auth, Cache;

class CachePage
{
    /**
     * Respond a cached page. If not available, cache the new response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
	public function handle($request, Closure $next, $time = null, $keyBy = null)
    {
		if (config('cachepage.allowSkipping') && $request->input('skipcache'))
			return $next($request);
		
		if (!$time)
			$time = config('cachepage.time');
	
		if (config('cachepage.allowFlushing') && $request->input('flushcache')) {
			if(Cache::getStore() instanceof \Illuminate\Cache\TaggableStore)
				Cache::tags('gpcachepage')->flush();
			else
				Cache::flush();
		}

		$key = urlencode($request->fullUrl());
		
		if ($keyBy) {
			if (Auth::check()) {
				$keyByChain = explode('.', $keyBy);
				$keyGetter = Auth::user();
				
				foreach ($keyByChain as $method)
					$keyGetter = $keyGetter->$method;
					
				$key = 'user='.$keyGetter.'&url='.$key;
			} else
				$key = 'user=guest&url='.$key;
		}
		
		if(Cache::getStore() instanceof \Illuminate\Cache\TaggableStore) {
			if (config('cachepage.allowClearing') && $request->input('clearcache'))
				Cache::tags('gpcachepage')->forget($key);

			if (Cache::tags('gpcachepage')->has($key)) {
				$cached_response = Cache::tags('gpcachepage')->get($key);

				if (strlen($cached_response) > 0)
					return response(Cache::tags('gpcachepage')->get($key));
			}

			$response = $next($request);

			if ($response->status() == 200 && strlen($response->getContent()) > 0)
				Cache::tags('gpcachepage')->put($key, $response->getContent(), $time);
		}
		else
		{
			if (config('cachepage.allowClearing') && $request->input('clearcache'))
				Cache::forget($key);
		
			if (Cache::has($key)) {
				$cached_response = Cache::get($key);

				if (strlen($cached_response) > 0)
					return response(Cache::get($key));
			}

			$response = $next($request);
	
			if ($response->status() == 200 && strlen($response->getContent()) > 0)
				Cache::put($key, $response->getContent(), $time);
		}

        return $response;
    }
}
