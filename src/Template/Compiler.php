<?php

declare(strict_types=1);

namespace Adm\Template;

use Adm\Template\Engine\Engine;
use Adm\Template\Node\Node;
use LogicException;

use function addcslashes;
use function hash;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function setlocale;
use function sprintf;
use function str_repeat;
use function var_export;

use const LC_NUMERIC;

class Compiler
{
    private ?string $source  = '';
    private int $indentation = 0;
    private Engine $engine;

    public function __construct(Engine $engine)
    {
        $this->engine = $engine;
    }

    public function getEngine(): Engine
    {
        return $this->engine;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function raw(string $string)
    {
        $this->source .= $string;

        return $this;
    }

    public function write(string ...$strings)
    {
        foreach ($strings as $string) {
            $this->source .= str_repeat(' ', $this->indentation * 4) . $string;
        }

        return $this;
    }

    public function string(string $value)
    {
        $this->source .= sprintf('"%s"', addcslashes($value, "\0\t\"\$\\"));

        return $this;
    }

    public function repr($value)
    {
        if (is_int($value) || is_float($value)) {
            if (false !== $locale = setlocale(LC_NUMERIC, '0')) {
                setlocale(LC_NUMERIC, 'C');
            }

            $this->raw(var_export($value, true));

            if (false !== $locale) {
                setlocale(LC_NUMERIC, $locale);
            }
        } elseif (null === $value) {
            $this->raw('null');
        } elseif (is_bool($value)) {
            $this->raw($value ? 'true' : 'false');
        } elseif (is_array($value)) {
            $this->raw('array(');
            $first = true;
            foreach ($value as $key => $v) {
                if (! $first) {
                    $this->raw(', ');
                }
                $first = false;
                $this->repr($key);
                $this->raw(' => ');
                $this->repr($v);
            }
            $this->raw(')');
        } else {
            $this->string($value);
        }

        return $this;
    }

    public function indent(int $step = 1)
    {
        $this->indentation += $step;

        return $this;
    }

    public function outdent(int $step = 1)
    {
        // can't outdent by more steps than the current indentation level
        if ($this->indentation < $step) {
            throw new LogicException('Unable to call outdent() as the indentation would become negative.');
        }

        $this->indentation -= $step;

        return $this;
    }

    public function getVarName(): string
    {
        return sprintf('__internal_%s', hash('sha256', __METHOD__ . $this->varNameSalt++));
    }

    /**
     * @param Node $node
     * @param int $indentation
     * @return $this
     */
    public function compile(Node $node, int $indentation = 0): self
    {
        $this->source      = '';
        $this->indentation = $indentation;

        $node->compile($this);

        return $this;
    }
}
