<?php

declare(strict_types=1);

namespace Adm\Template\Cache;

use RuntimeException;

use function basename;
use function chmod;
use function clearstatcache;
use function dirname;
use function file_exists;
use function file_put_contents;
use function filemtime;
use function hash;
use function is_dir;
use function is_null;
use function is_writable;
use function mkdir;
use function rename;
use function sprintf;
use function tempnam;
use function umask;

use const DIRECTORY_SEPARATOR;

class FileSystemCache implements CacheInterface
{
    protected string $cache;
    protected string $prefix = '__AdTemp__';

    public function __construct(?string $directory = null)
    {
        $this->cache = ! is_null($directory) ? $directory : ROOT . DIRECTORY_SEPARATOR . 'var/Cache/Core/Template/';
    }

    /**
     * Generate Hash File Name for
     * Template
     * Uses haval256 as this is the fastest hash for 64 char length
     * @param string $template
     * @return string
     */
    public function makeKey(string $template): string
    {
        $hash = hash('haval256,3', $template);
        return $this->cache . $hash[0] . $hash[1] . DIRECTORY_SEPARATOR . $hash . '.php';
    }

    /**
     * Include pre-compiled template
     * @param string $key
     */
    public function load(string $key): void
    {
        if (file_exists($key)) {
            @include_once $key;
        }
    }

    /**
     * Cache compiled template
     * @param string $key
     * @param string $content
     */
    public function cacheTemplate(string $key, string $content)
    {
        $dir = dirname($key);
        if (! is_dir($dir)) {
            if (false === @mkdir($dir, 0777, true)) {
                clearstatcache(true, $dir);
                if (! is_dir($dir)) {
                    throw new RuntimeException(sprintf('Unable to create the cache directory (%s).', $dir));
                }
            }
        } elseif (! is_writable($dir)) {
            throw new RuntimeException(sprintf('Unable to write in the cache directory (%s).', $dir));
        }

        $tmpFile = tempnam($dir, basename($key));
        if (false !== @file_put_contents($tmpFile, $content) && @rename($tmpFile, $key)) {
            @chmod($key, 0666 & ~umask());
            return;
        }

        throw new RuntimeException(sprintf('Failed to write cache file "%s".', $key));
    }

    /**
     * Get compiled template timestamp
     * @param string $key
     * @return int
     */
    public function getTimestamp(string $key): int
    {
        if (! file_exists($key)) {
            return 0;
        }

        return (int) @filemtime($key);
    }

    /**
     * Get cached template classname prefix
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
