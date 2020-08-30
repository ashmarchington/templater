<?php

declare(strict_types=1);

namespace Adm\Template\Cache;

interface CacheInterface
{
    public function makeKey(string $template): string;

    public function load(string $key): void;

    public function cacheTemplate(string $key, string $content);

    public function getTimestamp(string $key): int;
}
