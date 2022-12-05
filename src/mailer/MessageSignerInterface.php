<?php

declare(strict_types=1);

namespace mailer;

use Symfony\Component\Mime\Message;

interface MessageSignerInterface
{
    public function sign(Message $message, array $options = []): Message;
}
