<?php

declare(strict_types=1);

namespace Adm\Template\Engine;

use Adm\Template\Cache\CacheBuilder;
use Adm\Template\Cache\CacheInterface;
use Adm\Template\Compiler;
use Adm\Template\Exception\Message;
use Adm\Template\Exception\TemplateLoaderException;
use Adm\Template\Loader\LoaderInterface;
use Adm\Template\Source;
use Adm\Template\Template;
use Adm\Template\TemplateWrapper;

use Psr\Container\ContainerInterface;

use function class_exists;
use function hash;

class Engine
{
    public const NULL_CACHE = 1;

    protected LoaderInterface $loader;
    protected CacheInterface $cache;
    protected ContainerInterface $container;

    public function __construct(LoaderInterface $loader, CacheInterface $cache, ContainerInterface $container)
    {
        $this->loader = $loader;
        $this->cache  = $cache;
        $this->container = $container;
    }

    public function getLoader(): LoaderInterface
    {
        return $this->loader;
    }

    public function getTemplateDirectory(): array
    {
        return $this->loader->getPaths();
    }

    /**
     * @param string $template
     * @return false|string
     * @throws TemplateLoaderException
     */
    public function render(string $template)
    {
        return $this->load($template)->render($this->container);
    }

    /**
     * @return string
     */
    public function getTemplateClass(string $template)
    {
        return $this->cache->getPrefix() . hash('haval256,3', $template);
    }

    /**
     * @param $template
     * @throws TemplateLoaderException
     */
    public function load($template): TemplateWrapper
    {
        if ($template instanceof TemplateWrapper) {
            return $template;
        }
        return new TemplateWrapper($this->loadTemplate($this->getTemplateClass($template), $template));
    }

    /**
     * @throws TemplateLoaderException
     */
    private function loadTemplate(string $className, string $name): Template
    {
        $mainClass = $className;
        if (! class_exists($className, false)) {
            $key = $this->cache->makeKey($name);
            if ($this->getLoader()->isFresh($name, $this->cache->getTimestamp($key))) {
                $this->cache->load($key);
            }
            if (! class_exists($className, false)) {
                $source       = new Source(@require_once $this->getLoader()->findTemplate($name), $name, $this->getLoader()->findTemplate($name));
                $cacheBuilder = new CacheBuilder($this->getLoader(), new Compiler($this), $source);
                $content      = $cacheBuilder->buildCacheTemplate();
                $this->cache->cacheTemplate($key, $content);
                $this->cache->load($key);

                if (! class_exists($mainClass, false)) {
                    eval('?>' . $content);
                }
            }
            if (! class_exists($className, false)) {
                throw new TemplateLoaderException(new Message('Failed to load template: {0}. Cache may be corrupted', [$name]));
            }
        }
        return new $className();
    }
}
