<?php

declare(strict_types=1);

namespace Adm\Template\Test\stub\Blocks;

class ExampleBlock
{
    protected string $hello = 'Hello ';
    protected string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function sayHello(): string
    {
        return $this->hello . $this->name;
    }
}
