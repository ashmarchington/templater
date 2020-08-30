<?php

declare(strict_types=1);

namespace Adm\Template\Exception;

use MessageFormatter;

final class Message
{
    protected string $message;

    protected array $arguments;

    public function __construct(string $message, array $arguments)
    {
        $this->message   = $message;
        $this->arguments = $arguments;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function render(): string
    {
        return MessageFormatter::formatMessage('en_GB', $this->getMessage(), $this->getArguments());
    }
}
