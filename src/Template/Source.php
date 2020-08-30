<?php

declare(strict_types=1);

namespace Adm\Template;

class Source
{
    private array $template;
    private string $name;
    private string $path;

    public function __construct(array $template, string $name, string $path)
    {
        $this->template = $template;
        $this->name     = $name;
        $this->path     = $path;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTemplate(): array
    {
        return $this->template;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
