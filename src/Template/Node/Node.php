<?php

declare(strict_types=1);

namespace Adm\Template\Node;

use Adm\Template\Compiler;
use Adm\Template\Source;

class Node
{
    private array $nodes;
    private Source $source;

    public function __construct(StringNode $head, StringNode $body, StringNode $footer, ClassNode $block, Source $source)
    {
        $node         = [
            'head'   => $head,
            'body'   => $body,
            'footer' => $footer,
            'block'  => $block,
        ];
        $this->nodes  = $node;
        $this->source = $source;
    }

    /**
     * @return StringNode[]|ClassNode[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function compile(Compiler $compiler)
    {
        $this->compileTemplate($compiler);
    }

    private function compileTemplate(Compiler $compiler)
    {
        /**
         * Compile Class Start
         */
        $compiler->write('<?php');
        $compiler->write("\n");
        $compiler->write('declare(strict_types=1);');
        /**
         * Compile Class Header
         */
        $this->compileClassHeader($compiler);
        /**
         * Compile Class Functions
         */
        $this->compileClassFunction($compiler);
        /**
         * Compile Class Closure
         */
        $compiler
            ->outdent()
            ->write("}");
    }

    private function compileClassHeader(Compiler $compiler)
    {
        $compiler
            ->write("\n\n")
            ->write("use Admarch\Core\Template\Template;\n\n");
        $compiler
            ->write('class ' . $compiler->getEngine()->getTemplateClass($this->source->getName()))
            ->raw(" extends Template\n")
            ->write("{\n")
            ->indent()
            ->write("protected string \$templateName = '" . $this->source->getName() . "';\n\n");
        $compiler
            ->write("protected array \$content = [\n")
            ->indent();

        $compiler
            ->write("Template::HEAD => ")
            ->raw("'" . $this->getNodes()['head']->__toString() . "',\n");
        $compiler
            ->write("Template::BODY => ")
            ->raw("'" . $this->getNodes()['body']->__toString() . "',\n");
        $compiler
            ->write("Template::FOOTER => ")
            ->raw("'" . $this->getNodes()['footer']->__toString() . "',\n");
        $compiler
            ->write("Template::BLOCKS => [\n")
            ->indent();
        foreach ($this->getNodes()['block']->getClasses() as $var => $class) {
            $compiler
                ->write("'$var' => $class::class,\n");
        }
        $compiler
            ->outdent()
            ->write("]\n");
        $compiler
            ->outdent()
            ->write("];\n\n");
    }

    private function compileClassFunction(Compiler $compiler)
    {
        $compiler
            ->write("public function getExtends() : ?string\n")
            ->write("{\n")
            ->indent()
            ->write("return '" . $this->source->getTemplate()['extends'] . "';\n")
            ->outdent()
            ->write("}\n\n");
        $compiler
            ->write("public function getTemplateName() : string\n")
            ->write("{\n")
            ->indent()
            ->write("return \$this->templateName;\n")
            ->outdent()
            ->write("}\n\n");
    }
}
