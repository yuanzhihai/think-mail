<?php
/**
 * think-mail [A powerful and beautiful php mailer for All of ThinkPHP and Other PHP Framework based SwiftMailer]
 *
 * @author    yzh52521
 * @link      https://github.com/yzh52521/think-mail
 * @copyright 2019 yzh52521 all rights reserved.
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

namespace mailer\lib;

use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Class Transport
 * @package mailer\lib
 */
class Transport
{
    /**
     * @var TransportInterface|array Symfony transport instance or its array configuration.
     */
    private $_transport = [];


    /**
     * @return TransportInterface
     */
    public function getTransport(): TransportInterface
    {
        if (!is_object($this->_transport)) {
            $this->_transport = $this->createTransport($this->_transport);
        }
        return $this->_transport;
    }

    private function createTransport(array $config = []): TransportInterface
    {
        $defaultFactories = \Symfony\Component\Mailer\Transport::getDefaultFactories(null, null, null);
        $transportObj     = new \Symfony\Component\Mailer\Transport($defaultFactories);

        if (array_key_exists('dsn', $config)) {
            $transport = $transportObj->fromString($config['dsn']);
        } elseif (array_key_exists('scheme', $config) && array_key_exists('host', $config)) {
            $dsn       = new Dsn(
                $config['scheme'],
                $config['host'],
                $config['username'] ?? '',
                $config['password'] ?? '',
                $config['port'] ?? '',
                $config['options'] ?? [],
            );
            $transport = $transportObj->fromDsnObject($dsn);
        } else {
            $transport = $transportObj->fromString('null://null');
        }
        return $transport;
    }

}
