# PHP filesystem backed cache

This class provides an easy fs-backed cache.

[Read more (es)](http://emiliocobos.net/php-cache/) | [Contributors](https://github.com/ecoal95/php-cache/graphs/contributors)

## Starting

You can see an easy example in the `examples/` dir.

You can also install this package with `composer`.

## Configuration

There are two main options: `cache_path` y `expires`.

* `cache_path` is the directory where cache will be stored. It's a relative
    directory by default (`cache`), but it's recommendable to re-configure it.
* `expires` is the cache expiration time **in minutes**.

**Important**: `cache_path` should be writable (that's obvious), but if it's
public in the server, which is not recommended, you should forbid access to it.

A way to do it for Apache is having a `.htaccess` as follows in the cache dir:

```
deny from all
```


## Usage

To store any data type you should use the `put` method using an identifier, and
the value.

```php
Cache::put('key', 'value');
```


### Retrieving data

To get data stored in the cache you should use:

```php
Cache::get('key');
```

If the item is not found, or it's expired, it will return `null`.

#### Raw data

You can store and retrieve raw data, to prevent decoding and encoding overhead.

You should specify it's raw data in both  `put()` and `get()`, as follows:

```php
Cache::put($key, $big_chunk_of_data, true);
// ...
Cache::get($key, true);
```

### Deleting data

You may delete a single item from de cache using the `delete()` method:

```php
Cache::delete($key);
```

You may also flush all the cache to delete everything:

```php
Cache::flush();
```

## Race conditions

This library makes atomic writes via `rename`, so no race condition should be
possible.

## Performance

There are some benchmarks over the `benchmarks/` directory.

They're simple ones and you're encouraged to write more extensive ones. In
general, **the performance difference compared with memcached or apc is almost
unnoticeable with large chunks of data, but it's relatively high with small data
fragments**. That's expected due to the fs access overhead.

There are also legacy tests by a contributor in the `legacy-tests` folder which
test performance and race-condition ressistance.
