<?php

declare(strict_types=1);

namespace Adm\Template;

use Adm\Template\Exception\Message;
use Adm\Template\Exception\TemplateLoaderException;
use Exception;
use Psr\Container\ContainerInterface;

use function extract;
use function ob_end_clean;
use function ob_get_clean;
use function ob_get_contents;
use function ob_get_level;
use function ob_start;

/**
 * Defines the standard functions shared by all templates.
 *
 * Once render has been called by the engine it will concatenate the contents
 * of get methods
 *
 * @internal
 */
abstract class Template
{
    public const HEAD   = '__head__';
    public const BODY   = '__body__';
    public const FOOTER = '__footer__';
    public const BLOCKS = '__blocks__';

    protected string $templateName;

    protected array $content = [
        self::HEAD   => '',
        self::BODY   => '',
        self::FOOTER => '',
        self::BLOCKS => [],
    ];

    abstract public function getExtends(): ?string;

    abstract public function getTemplateName(): string;

    public function getHead(): string
    {
        return $this->content[self::HEAD];
    }

    public function getBody(): string
    {
        return $this->content[self::BODY];
    }

    public function getFooter(): string
    {
        return $this->content[self::FOOTER];
    }

    public function getBlocks(): ?array
    {
        return $this->content[self::BLOCKS];
    }

    public function render(ContainerInterface $container)
    {
        $level = ob_get_level();
        ob_start(function () {
            return '';
        });
        try {
            foreach ($this->getBlocks() as $key => $value) {
                $this->content[self::BLOCKS][$key] = $container->get($value);
            }
            extract($this->getBlocks());
            echo "<!DOCTYPE html>\n<html>\n<head>\n";
            eval("?>" . $this->getHead());
            echo "\n</head>\n<body>\n";
            eval("?>" . $this->getBody());
            echo "\n</body>\n";
            eval("?>" . $this->getFooter());
            echo "\n</html>";
            $content = ob_get_contents();
        } catch (Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw new TemplateLoaderException(new Message('Template {0} render failed: {1}', [$this->getTemplateName(), $e->getMessage()]));
        }
        ob_get_clean();
        return $content;
    }
}
