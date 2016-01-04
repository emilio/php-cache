<?php
namespace Cache;

/**
 * Simple file cache
 *
 * This class is great for those who can't use apc or memcached in their proyects.
 *
 * @author Emilio Cobos (emiliocobos.net) <ecoal95@gmail.com> and github contributors
 * @version 1.0.1
 * @link http://emiliocobos.net/php-cache/
 *
 */
class Cache
{
    /**
     * Configuration
     *
     * @access public
     */
    public static $config = array(
        'cache_path' => 'cache',
        // Default expiration time in *minutes*
        'expires' => 180,
    );

    /**
     * Lets you configure the cache properly, passing an array:
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
     * @param mixed $key the array with de configuration or the key as string
     * @param mixed $val the value for the previous key if it was an string
     * @return void
     */
    public static function configure($key, $val = null)
    {
        if (is_array($key)) {
            foreach ($key as $config_name => $config_value) {
                self::$config[$config_name] = $config_value;
            }
        } else {
            self::$config[$key] = $val;
        }
    }

    /**
     * Get a route to the file associated to that key.
     *
     * @access private
     * @param string $key
     * @return string the filename of the php file
     */
    private static function getRoute($key)
    {
        return static::$config['cache_path'] . '/' . md5($key) . '.php';
    }

    /**
     * Get the data associated with a key
     *
     * @access public
     * @param string $key
     * @return mixed the content you put in, or null if expired or not found
     */
    public static function get($key, $raw = false, $custom_time = null)
    {
        if (! self::fileExpired($file = self::getRoute($key), $custom_time)) {
            $content = file_get_contents($file);
            return $raw ? $content : unserialize($content);
        }

        return null;
    }

    /**
     * Put content into the cache
     *
     * @access public
     * @param string $key
     * @param mixed $content the the content you want to store
     * @param bool $raw whether if you want to store raw data or not. If it is true, $content *must* be a string
     *        It can be useful for static html caching.
     * @return bool whether if the operation was successful or not
     */
    public static function put($key, $content, $raw = false)
    {
        $dest_file_name = self::getRoute($key);

        /** Use a unique temporary filename to make writes atomic with rewrite */
        $temp_file_name = str_replace(".php", uniqid("-", true).".php", $dest_file_name);

        $ret = @file_put_contents($temp_file_name, $raw ? $content : serialize($content));

        if ($ret === false) {
            @unlink($temp_file_name);
            return false;
        }

        return @rename($temp_file_name, $dest_file_name);
    }

    /**
     * Delete data from cache
     *
     * @access public
     * @param string $key
     * @return bool true if the data was removed successfully
     */
    public static function delete($key)
    {
        return @unlink(self::getRoute($key));
    }

    /**
     * Flush all cache
     *
     * @access public
     * @return bool always true
     */
    public static function flush()
    {
        $cache_files = glob(self::$config['cache_path'] . '/*.php', GLOB_NOSORT);
        foreach ($cache_files as $file) {
            @unlink($file);
        }
        return true;
    }

    /**
     * Check if a file has expired or not.
     *
     * @access private
     * @param $file the rout to the file
     * @param int $time the number of minutes it was set to expire
     * @return bool if the file has expired or not
     */
    private static function fileExpired($file, $time = null)
    {
        if (! file_exists($file)) {
            return true;
        }
        return (time() > (filemtime($file) + 60 * ($time ? $time : self::$config['expires'])));
    }
}
