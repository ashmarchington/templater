<?php

declare(strict_types=1);

namespace Adm\Template\Node;

use ArrayIterator;
use Countable;
use IteratorAggregate;

use function count;

class ClassNode implements Countable, IteratorAggregate
{
    private array $classes;

    public function __construct(array $classes)
    {
        $this->classes = $classes;
    }

    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->getClasses());
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getClasses());
    }
}
