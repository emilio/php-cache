<?php
namespace EC\Storage;

/**
 * Simple file cache.
 *
 * This class is great for those who can't use apc or memcached in their proyects.
 *
 * @author Emilio Cobos (emiliocobos.net) <ecoal95@gmail.com> and github contributors
 *
 * @version 1.0.1
 *
 * @link http://emiliocobos.net/php-cache/
 */
class Cache
{
    /**
     * Configuration with default values.
     */
    private static $cache_path = 'cache';
    private static $expires = 180;
    private static $file_prefix = '';
    private static $file_suffix = '.cache';

    /**
     * Lets you configure the cache properly, passing an array:.
     *
     * <code>
     * Cache::configure(array(
     *   'expires' => 180,
     *   'cache_path' => 'cache'
     * ));
     * </code>
     * Or passing a key/val:
     *
     * <code>
     * Cache::configure('expires', 180);
     * </code>
     *
     * @access public
     * 
     * @param mixed $key the array with de configuration or the key as string
     * @param mixed $val the value for the previous key if it was an string
     * 
     * @return void
     */
    public static function configure($key, $val = null)
    {
        if (is_array($key)) {
            foreach ($key as $config_name => $config_value) {
                self::configure($config_name, $config_value);
            }
        } else {
            if (isset(self::$$key)) {
                self::$$key = $val;
            } else {
                throw new IllegalArgumentException("No existe $key.");
            }
        }
    }

    /**
     * Get a route to the file associated to that key.
     *
     * @access private
     * 
     * @param string $key
     *
     * @return string the filename of the php file
     */
    private static function get_route($key, $prefix = null, $suffix = null)
    {
        if ($prefix == null) {
            $prefix = self::$file_prefix;
        }

        if ($suffix == null) {
            $suffix = self::$file_suffix;
        }

        return self::$cache_path.DIRECTORY_SEPARATOR.$prefix.md5($key).$suffix;
    }

    /**
     * Get the data associated with a key.
     *
     * @access public
     * 
     * @param string $key
     *
     * @return mixed the content you put in, or null if expired or not found
     */
    public static function get($key, $raw = false, $custom_time = null)
    {
        $file = self::get_route($key);

        if (!self::file_expired($file, $custom_time)) {
            $content = file_get_contents($file);

            return $raw ? $content : unserialize($content);
        }

        return;
    }

    /**
     * Put content into the cache.
     *
     * @access public
     * 
     * @param string $key
     * @param mixed  $content the the content you want to store
     * @param bool   $raw     whether if you want to store raw data or not. If it is true, $content *must* be a string
     *                        It can be useful for static html caching.
     *
     * @return bool whether if the operation was successful or not
     */
    public static function put($key, $content, $raw = false)
    {
        $dest_file_name = self::get_route($key);

        /* Use a unique temporary filename to make writes atomic with rewrite */
        $temp_file_name = self::get_route($key, 'temp_', uniqid('-', true).self::$file_suffix);

        if (@file_put_contents($temp_file_name, $raw ? $content : serialize($content)) !== false) {
            if (@rename($temp_file_name, $dest_file_name)) {
                return true;
            } else {
                @unlink($temp_file_name);
            }
        }

        return false;
    }

    /**
     * Delete data from cache.
     *
     * @access public
     * 
     * @param string $key
     *
     * @return bool true if the data was removed successfully
     */
    public static function delete($key)
    {
        return @unlink(self::get_route($key));
    }

    /**
     * Flush all cache.
     * 
     * @access public
     *
     * @return bool true if all cache files were removed successfully
     */
    public static function flush()
    {
        $ret = true;

        $cache_files = glob(self::$cache_path.DIRECTORY_SEPARATOR.self::$file_prefix.'*'.self::$file_suffix, GLOB_NOSORT);

        foreach ($cache_files as $file) {
            if (!@unlink($file)) {
                $ret = false;
            }
        }

        return $ret;
    }

    /**
     * Check if a file has expired or not.
     * 
     * @access private
     *
     * @param $file the rout to the file
     * @param int $time the number of minutes it was set to expire
     *
     * @return bool if the file has expired or not
     */
    private static function file_expired($file, $time = null)
    {
        if (!file_exists($file)) {
            return true;
        }

        if (!$time) {
            $time = self::$expires;
        }

        return time() > (filemtime($file) +  $time);
    }
}
?>
