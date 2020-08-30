<?php

declare(strict_types=1);

namespace Adm\Template;

final class Content
{
    protected string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }
}
