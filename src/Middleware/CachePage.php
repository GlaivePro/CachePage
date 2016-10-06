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
	
		if (config('cachepage.allowFlushing') && $request->input('flushcache'))
		{
			if(Cache::getStore() instanceof \Illuminate\Cache\TaggableStore)
				Cache::tags('gpcachepage')->flush();
			else
				Cache::flush();
		}
		
		$key = urlencode($request->url());
		
		if ($keyBy)
		{
			if (Auth::check())
			{
				$keyByChain = explode('.', $keyBy);
				$keyGetter = Auth::user();
				
				foreach ($keyByChain as $method)
					$keyGetter = $keyGetter->$method;
					
				$key = 'keyByChain='.$keyGetter.'&url='.$key;
			}
			else
				$key = 'user=guest&url='.$key;
		}
		
		if(Cache::getStore() instanceof \Illuminate\Cache\TaggableStore)
		{
			if (config('cachepage.allowClearing') && $request->input('clearcache'))
				Cache::tags('gpcachepage')->forget($key);

			if (Cache::tags('gpcachepage')->has($key))
				return response(Cache::tags('gpcachepage')->get($key));

			$response = $next($request);

			Cache::tags('gpcachepage')->put($key, $response->getContent(), $time);
		}
		else
		{
			if (config('cachepage.allowClearing') && $request->input('clearcache'))
				Cache::forget($key);
			
			if (Cache::has($key))
				return response(Cache::get($key));

			$response = $next($request);

			Cache::put($key, $response->getContent(), $time);
		}

        return $response;
    }
}
