<?php

declare(strict_types=1);

namespace Adm\Template\Exception;

use Exception;

class BaseException extends Exception
{
    protected Message $phrase;

    public function __construct(Message $phrase, ?parent $cause = null, $code = 0)
    {
        $this->phrase = $phrase;
        parent::__construct($this->phrase->render(), $code, $cause);
    }
}
