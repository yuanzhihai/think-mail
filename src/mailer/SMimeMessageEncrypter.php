<?php

declare( strict_types = 1 );

namespace mailer;

use Symfony\Component\Mime\Crypto\SMimeEncrypter;
use Symfony\Component\Mime\Message;

/**
 * @codeCoverageIgnore This class is a trivial proxy that requires no testing
 */
class SMimeMessageEncrypter implements MessageEncrypterInterface
{
    private SMimeEncrypter $encrypter;

    public function __construct(SMimeEncrypter $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    public function encrypt(Message $message): Message
    {
        return $this->encrypter->encrypt( $message );
    }
}
