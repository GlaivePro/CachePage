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

		$key = $this->getKey($request, $keyBy);
		if (!$key)
			return $next($request);
	
		$cache = cache();
		if ($cache->getStore() instanceof \Illuminate\Cache\TaggableStore)
			$cache = $cache->tags('gpcachepage');

		if (!$time)
			$time = config('cachepage.time');
	
		if (config('cachepage.allowFlushing') && $request->input('flushcache'))
			$cache->flush();
		
		if (config('cachepage.allowClearing') && $request->input('clearcache'))
			$cache->forget($key);

		if ($cache->has($key)) {
			$cached_response = $cache->get($key);

			if (strlen($cached_response) > 0)
				return response($cached_response));
		}

		$response = $next($request);

		if ($response->status() == 200 && strlen($response->getContent()) > 0)
			$cache->put($key, $response->getContent(), $time);

        return $response;
    }

	protected function getKey($request, $keyBy)
	{
		$key = urlencode($request->fullUrl());

		if (!$keyBy)
			return $key;
		
		if (Auth::guest())
			return 'user=guest&url='.$key;
		
		// Special key that allows skipping cache for authenticated users.
		if ('NULL' === $keyBy)
			return null;

		$keyBy = explode('.', $keyBy);
		$keyGetter = Auth::user();
		
		foreach ($keyBy as $prop)
			$keyGetter = $keyGetter->$prop;
			
		return 'user='.$keyGetter.'&url='.$key;
	}
}
