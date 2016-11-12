# CachePage

Laravel middleware for full page caching.


## Table of Contents

- [How do I get this in my app?](#how-do-i-get-this-in-my-app)
    - [Composer](#composer)
    - [Getting files manually](#getting-files-manually)
    - [Binding it to your app](#binding-it-to-your-app)
- [How do I use it?](#how-do-i-use-it)
    - [Middleware](#middleware)
    - [User-specific caching](#user-specific-caching)
- [Other concerns](#other-concerns)
    - [Can I skip it?](#can-i-skip-it)
    - [How to clear the cache?](#how-to-clear-the-cache)
    - [When should I use it?](#when-should-i-use-it)
- [Changelog](#changelog)
- [License](#license)

## How do I get this in my app?

### Composer

Open your `copmoser.json` file, find the `require` key and add this value :

    "glaivepro/cachepage": "^1.0.0"
    
Execute `composer update`.

### Getting files manually

If, for some reason you can't or don't want to download package using composer, you should download the files and put all of the contents in `/vendor/glaivepro/cachepage/` folder.

Add this to your `composer.json` file (if the autoload and psr-4 keys are already there, just leave them and the contents and only add what's missing):

```json
	"autoload": {
        "psr-4": {
            "GlaivePro\\CachePage\\": "vendor/glaivepro/cachepage/src/GlaivePro/CachePage",
        }
    }
```
	
Run `composer dump-autoload` command.
	
	
### Binding it to your app

Firt of all you have to register the service provider. 

Open `config/app.php` and find the `providers` key. Add this line to the array.

```php
	...
	GlaivePro\CachePage\CachePageServiceProvider::class,
	...
```

If you will need to adjust the behaviour of the package, you have to publish the configuration file according to your application. Just execute this artisan command:
    
	php artisan vendor:publish --provider="GlaivePro\CachePage\CachePageServiceProvider"
	
And you will have the `config/cachepage.php` file to change whenever this manual tells you to adjust something in the configuration.


## How do I use it?

### Middleware

Add the `gpcachepage` middleware to a route and all responses will be cached for the configured time. By default it's 5 minutes, but you can change it in the configuration.

```php
    Route::get('/', 'PageController@welcome')->middleware('gpcachepage');
```

The response will be cached using Laravels `Cache` functionality you should configure it. It will be keyed by requests url and tagged with `gpcachepage` tag (if your cache driver supports tagging).

You can specify time if you want to. For example, to cache a page for two hours:

```php
    Route::get('contacts', 'PageController@contacts')->middleware('gpcachepage:120');
```

Or make a group of routes that cache their responses for 5 hours:

```php
    Route::group(['middleware' => ['gpcachepage:300']], function () {
        Route::get('about', 'PageController@about');
        Route::get('policy', 'PageController@policy');
    });
```
For further advice regarding usage of middlewares you should read the [Laravel documentation about middlewares](https://laravel.com/docs/master/middleware).


### User-specific caching

Sometimes the page should be different for different users. For example, you might want to display the users name at top - you will want users to see their own name there, not the cached one, right?

You can do it like this:

```php
    Route::get('contacts', 'PageController@contacts')->middleware('gpcachepage:120,id');
```

Specifying time in this case is unavoidable. The key for caching will be something like `id=153&url=http://mypage.com`.

If you want to key by users role or some other field (or related field), you can try to pass them like this:
```php
	Route::get('contacts', 'PageController@contacts')->middleware('gpcachepage:120,role.name');
```

The middleware will then try to get a value from `Auth::user()->role->name`.


## Other concerns

### Can I skip it?

If you want to skip caching and just view a freshly made page, pass `skipcache=true` as a HTTP parameter, for example:

    http://mypage.com/contacts?skipcache=true
	
You can also use `1` instead of `true` if you prefer it.

If you are afraid that users might abuse this, you can disable this functionality in configuration like this:
```php
	'allowSkipping' => false,
```


### How to clear the cache?

If you want to clear the cached page that you are seeing, specify a true `clearcache` in the HTTP request:

    http://mypage.com/contacts?clearcache=1

The cache for page that you are seeing will be erased. However, if you are using user-specific caching, other users caches will not be reset. If you don't want this to be possible, you can disable it in the config:
```php
	'allowClearing' => false,
```

If you update something like main menu or make changes to a page that you want all users to see, you can clear all of the cache using `flushcache` in the url.

    http://mypage.com/contacts?flushcache=1
	
This functionality is dangerous as someone can easily clear your cache all the time therefore it is disabled by default. If you decide to use it, you have to enable it in the configuration like this:
```php
	'allowFlushing' => true,
```

Beware! If possible, we use tags for caching. However, if you are using caching driver that does not support tagging, this flushing might clear all of your applications cache.


### When should I use it?

First of all, decide the maximum allowable caching time for a page. How often does the page change? How soon do you want the changes to be visible? Is it ok that a page will refresh once every minute? Once every 10 minutes? Once a week?

When you know the time, try to guess how many users will use a single cache. For example, if you cache a page for 2 minutes, how many users will view it during the time? If the page will receive 100 hits during the 2 minutes, sure it's worth caching. However some old article or a privacy policy page might be viewed less than once every 2 minutes, so no point caching those for 2 minutes as no users would use the cached version.

If you are using user-specific caching, take into account that as well. If each of your logged-in users see a different page, will they actually do enough hits to make the caching worth it?

## Changelog

It's [here](CHANGELOG.md).

## License

This package is licensed under the [MIT license](LICENSE.md).
