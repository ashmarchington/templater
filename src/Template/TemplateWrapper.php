<?php

declare(strict_types=1);

namespace Adm\Template;

use Psr\Container\ContainerInterface;

/**
 * Used instead of calling template directly
 *
 * Prevents template files having access to
 * sensitive data stored in $this when called
 * directly from Engine()
 */
class TemplateWrapper
{
    private Template $template;

    public function __construct(Template $template)
    {
        $this->template = $template;
    }

    public function render(ContainerInterface $container)
    {
        return $this->template->render($container);
    }

    public function getExtends()
    {
        return $this->template->getExtends();
    }

    public function getTemplateName()
    {
        return $this->template->getTemplateName();
    }
}
