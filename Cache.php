<?php
/*
 * Simple file cache
 * @author Emilio Cobos (emiliocobos.net) <ecoal95@gmail.com>
 * @version 1.0
 */
class Cache {
	/*
	 * Configuration
	 */
	public static $config = array(
		'cache_dir' => 'cache',
		// Default expiration time in *hours*
		'expires' => 3
	);

	/*
	 * Lets you configure the cache properly, passing an array
		<code>
		Cache::configure(array(
			'expires' => 3,
			'cache_path' => 'cache'
		));
		</code>
	 * Or passing a key/val
		<code>
		Cache::configure('expires', 3);
		</code>
	 * @param mixed $key the array with de configuration or the key as string
	 * @param mixed $val the value for the previous key if it was an string
	 * @return nothing
	 */
	public static function configure($key, $val = null) {
		if( is_array($key) ) {
			foreach ($key as $config_name => $config_value) {
				self::$config[$config_name] = $config_value;
			}
		} else {
			self::$config[$key] = $val;
		}
	}

	/*
	 * Get a route to the file associated to that key.
	 * @param string $key
	 * @return string the filename of the php file
	 */
	public static function get_route($key) {
		return static::$config['cache_path'] . '/' . md5($key) . '.php';
	}

	/*
	 * Get the data associated with a key
	 * @param string $key
	 * @return mixed the content you put in, or null if expired or not found
	 */
	public static function get($key = null, $raw = false) {
		if( ! $key ) {
			return null;
		}

		if( ! self::file_expired($file = self::get_route($key))) {
			$content = file_get_contents($file);
			return $raw ? $content : unserialize($content);
		} else {
			return null;
		}
	}

	/*
	 * Put content into the cache
	 * @param string $key
	 * @param mixed $content the the content you want to store
	 * @param bool $raw whether if you want to store raw data or not. If it is true, $content *must* be a string
	 *        It can be useful for static html caching.
	 * @return bool whether if the operation was successful or not
	 */
	public static function put($key = null, $content = null, $raw = false) {
		if( ! $content ) {
			return false;
		}
		return @file_put_contents(self::get_route($key), $raw ? $content : serialize($content)) !== false;
	}

	/*
	 * Delete data from cache
	 * @param string $key
	 * @return bool true if the data was removed successfully
	 */
	public static function delete($key = null) {
		if( ! file_exists($file = self::get_route($key)) ) {
			return true;
		}

		return @unlink($file);
	}

	/*
	 * Flush all cache
	 */
	public static function flush() {
		$cache_files = glob(self::$config['cache_path'] . '/*.php');
		foreach ($cache_files as $file) {
			@unlink($file);
		}
		return true;
	}

	/*
	 * Check if a file has expired or not.
	 * @param $file the rout to the file
	 * @param int $time the number of hours it was set to expire
	 * @return bool if the file has expired or not
	 */
	public static function file_expired($file, $time = null) {
		if( ! file_exists($file) ) {
			return true;
		}
		return (time() > (filemtime($file) + 60 * 60 * ($time ? $time : self::$config['expires'])));
	}
}