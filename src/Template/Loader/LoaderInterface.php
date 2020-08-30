<?php

declare(strict_types=1);

namespace Adm\Template\Loader;

use Adm\Template\Exception\TemplateLoaderException;

interface LoaderInterface
{
    public function __construct(array $paths = [], string $viewDirName = 'View', string $templateDirName = 'Template', ?string $root = null);

    public function getPaths(): array;

    public function setPaths(array $paths, string $namespace): void;

    /**
     * @return mixed
     * @throws TemplateLoaderException
     */
    public function findTemplate(string $name): string;
}
