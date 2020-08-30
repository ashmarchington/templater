<?php

declare(strict_types=1);

namespace Adm\Template\Node;

use Stringable;

use function array_key_last;

class StringNode implements Stringable
{
    /**
     * @var string $content The content of the node
     */
    private string $content = '';

    /**
     * @param string[] $content
     */
    public function __construct(array $content)
    {
        $last = array_key_last($content);
        foreach ($content as $key => $value) {
            if ($key !== $last) {
                $this->content .= $value . "\n";
            } else {
                $this->content .= $value;
            }
        }
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function __toString(): string
    {
        return $this->getContent();
    }
}
