<?php

declare(strict_types=1);

namespace Adm\Template\Loader;

use Adm\Template\Exception\Message;
use Adm\Template\Exception\TemplateLoaderException;

use function count;
use function file_exists;
use function file_get_contents;
use function filemtime;
use function getcwd;
use function is_dir;
use function realpath;
use function strlen;
use function strncmp;
use function substr;

use const DIRECTORY_SEPARATOR;

class SystemLoader implements LoaderInterface
{
    public const ROOT         = '__root__';
    public const MAIN         = '__main__';
    public const TEMPLATE_EXT = '.php';
    public const VIEW_EXT     = '.phtml';

    protected array $paths = [];
    private string $root;
    private string $viewDir;
    private string $tempDir;

    public function __construct(array $paths = [], string $viewDirName = 'View', string $templateDirName = 'Template', ?string $root = null)
    {
        $this->root = ($root ?? getcwd()) . DIRECTORY_SEPARATOR;
        if (false !== $realPath = realpath($this->root)) {
            $this->root = $realPath . DIRECTORY_SEPARATOR;
        }
        $this->setPaths($paths, self::MAIN);
        $this->tempDir = $templateDirName;
        $this->viewDir = $viewDirName;
    }

    /**
     * @return array
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * @return mixed
     */
    public function getRootPath(): string
    {
        return $this->paths[self::ROOT];
    }

    /**
     * @param array $paths
     * @param string $namespace
     */
    public function setPaths(array $paths, string $namespace = self::MAIN): void
    {
        foreach ($paths as $path) {
            try {
                $this->addPath($path, $namespace);
            } catch (TemplateLoaderException $e) {
            }
        }
    }

    /**
     * @param string $name
     * @return string
     * @throws TemplateLoaderException
     */
    public function findTemplate(string $name): string
    {
        foreach ($this->getPaths() as $path) {
            if (file_exists($path . DIRECTORY_SEPARATOR . $this->tempDir . DIRECTORY_SEPARATOR . $name . self::TEMPLATE_EXT)) {
                return $path . DIRECTORY_SEPARATOR . $this->tempDir . DIRECTORY_SEPARATOR . $name . self::TEMPLATE_EXT;
            }
        }
        throw new TemplateLoaderException(new Message('Template does not exist. Checked in: {0} locations', [count($this->getPaths())]));
    }

    /**
     * @param string $path
     * @param string $namespace
     * @throws TemplateLoaderException
     */
    private function addPath(string $path, string $namespace): void
    {
        if (! is_dir($path)) {
            throw new TemplateLoaderException(new Message('Path: {0}, is not a directory', [$path]));
        }
        $this->paths[$namespace] = $path;
    }

    /**
     * @param string $template
     * @return string
     * @throws TemplateLoaderException
     */
    public function getCacheKey(string $template): string
    {
        if (null === $path = $this->findTemplate($template)) {
            return '';
        }
        $len = strlen($this->root);
        if (0 === strncmp($this->root, $path, $len)) {
            return substr($path, $len);
        }

        return $path;
    }

    /**
     * @param string $view
     * @return mixed
     * @throws TemplateLoaderException
     */
    public function getView(string $view)
    {
        foreach ($this->getPaths() as $path) {
            if (file_exists($path . DIRECTORY_SEPARATOR . $this->viewDir . DIRECTORY_SEPARATOR . $view . self::VIEW_EXT)) {
                return $v = @file_get_contents($path . DIRECTORY_SEPARATOR . $this->viewDir . DIRECTORY_SEPARATOR . $view . self::VIEW_EXT);
            }
        }
        throw new TemplateLoaderException(new Message('No view found matching name: {0}', [$view]));
    }

    /**
     * @param string $name
     * @param int $time
     * @return bool
     * @throws TemplateLoaderException
     */
    public function isFresh(string $name, int $time): bool
    {
        $path = $this->findTemplate($name);

        return filemtime($path) < $time;
    }
}
