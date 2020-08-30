<?php

declare(strict_types=1);

namespace Adm\Template\Cache;

use Adm\Template\Compiler;
use Adm\Template\Exception\TemplateLoaderException;
use Adm\Template\Loader\LoaderInterface;
use Adm\Template\Node\ClassNode;
use Adm\Template\Node\Node;
use Adm\Template\Node\StringNode;
use Adm\Template\Source;

use function array_key_exists;
use function array_merge_recursive;
use function is_null;
use function usort;

class CacheBuilder
{
    /**
     * Declaration of types of node
     */
    private const HEAD   = 'head';
    private const BODY   = 'body';
    private const FOOTER = 'footer';
    private const BLOCKS = 'blocks';

    protected array $template;
    protected array $extend = [];
    protected LoaderInterface $loader;
    protected Compiler $compiler;
    protected Source $source;

    public function __construct(LoaderInterface $loader, Compiler $compiler, Source $source)
    {
        $this->loader   = $loader;
        $this->source   = $source;
        $this->compiler = $compiler;
        $this->template = $source->getTemplate();
        $this->ifExtends($loader, $this->template);
        $this->template = array_merge_recursive($this->template, $this->extend);
        unset($this->extend);
        unset($this->template['extends']);
        usort($this->template['layout']['head']['templates'], [$this, 'sortTemplates']);
        usort($this->template['layout']['body']['templates'], [$this, 'sortTemplates']);
        usort($this->template['layout']['footer']['templates'], [$this, 'sortTemplates']);
    }

    /**
     * Basic sort by priority
     *
     * @param $a
     * @param $b
     * @return bool
     */
    private function sortTemplates($a, $b)
    {
        return $a['priority'] > $b['priority'];
    }

    /**
     * Create the Template to be cached
     */
    public function buildCacheTemplate(): string
    {
        $node = new Node(
            $this->getNode(self::HEAD),
            $this->getNode(self::BODY),
            $this->getNode(self::FOOTER),
            $this->getNode(self::BLOCKS),
            $this->source
        );
        $node->compile($this->compiler);
        return $this->compiler->getSource();
    }

    /**
     * Dispatch to correct
     * method
     *
     * @return StringNode|ClassNode
     */
    private function getNode(string $nodeType)
    {
        $dispatch = [
            self::HEAD   => 'getStringNode',
            self::BODY   => 'getStringNode',
            self::FOOTER => 'getStringNode',
            self::BLOCKS => 'getClassNode',
        ];
        if (array_key_exists($nodeType, $dispatch)) {
            $method = $dispatch[$nodeType];
        }
        return $this->$method($nodeType);
    }

    /**
     * Create a new StringNode
     *
     * @see StringNode
     *
     * @return StringNode
     */
    private function getStringNode(string $type)
    {
        $temp = [];
        foreach ($this->template['layout'][$type]['templates'] as $template) {
            $temp[] = $this->loader->getView($template['name']);
        }
        return new StringNode($temp);
    }

    /**
     * Create a new ClassNode
     *
     * @see ClassNode
     *
     * @return ClassNode
     */
    private function getClassNode(string $type)
    {
        $classes = [];
        foreach ($this->template['layout'] as $section) {
            foreach ($section['templates'] as $template) {
                foreach ($template['blocks'] as $variable => $class) {
                    $classes[$variable] = $class;
                }
            }
        }
        return new ClassNode($classes);
    }

    /**
     * Find templates extended by current
     * template
     */
    public function ifExtends(LoaderInterface $loader, array $template)
    {
        if (! is_null($template['extends'])) {
            try {
                $temp = @include_once $loader->findTemplate($template['extends'] . '.php');
            } catch (TemplateLoaderException $e) {
            }
            if (! is_null($temp['extends'])) {
                $this->extend = array_merge_recursive($this->extend, $temp);
                $this->ifExtends($loader, $temp);
            } else {
                $this->extend = array_merge_recursive($this->extend, $temp);
            }
        } else {
            return;
        }
    }
}
