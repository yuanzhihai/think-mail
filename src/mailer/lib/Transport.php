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

use Swift_DependencyContainer;

/**
 * Class Transport
 * @package mailer\lib
 */
class Transport
{
    // 单例
    private static $instance;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * 创建一个smtp传输对象
     *
     * @param array $config 配置信息
     * @return mixed
     * @throws \Swift_DependencyException
     */
    public function createSmtpDriver($config = [])
    {
        $config = array_merge(Config::get(), $config);
        $transport = Swift_DependencyContainer::getInstance()->lookup('transport.smtp')
            ->setHost($config['host'])
            ->setPort($config['port'])
            ->setUsername($config['addr'])
            ->setPassword($config['pass'])
            ->setEncryption($config['security']);
        if (isset($config['stream'])) {
            $transport->setStreamOptions($config['stream']);
        }

        return $transport;
    }

    /**
     * 创建一个sendmail传输对象
     *
     * @param $sendmail null|string sendmail配置
     * @return mixed
     * @throws \Swift_DependencyException
     */
    public function createSendmailDriver($sendmail = null)
    {
        return Swift_DependencyContainer::getInstance()->lookup('transport.sendmail')
            ->setCommand($sendmail ?: Config::get('sendmail'));
    }

    /**
     * 创建一个mail传输对象
     *
     * @return array|mixed
     * @throws \Swift_DependencyException
     */
    public function createMailDriver()
    {
        return Swift_DependencyContainer::getInstance()->lookup('transport.mail');
    }

    /**
     * 获取邮件驱动
     *
     * @param mixed $driver 发送邮件驱动名称
     *
     * @return object
     * @throws Exception
     */
    public function getDriver($driver = null)
    {
        $driverName = $driver ? $driver : Config::get('driver');
        if (is_array($driverName)) {
            // 驱动为数组，表示类的某个方法
            if (!is_callable($driverName)) {
                throw new BadMethodCallException('Method Not Found: ' . $driverName[0] . '->' . $driverName[1] . '()');
            }

            return call_user_func_array($driverName, []);
        }

        if (is_object($driverName)) {
            // 驱动为对象直接返回
            return $driverName;
        }

        if (is_string($driverName)) {
            // 驱动为字符串，为内置驱动
            $driver = 'create' . ucfirst($driverName) . 'Driver';
            if (!method_exists($this, $driver)) {
                throw new BadMethodCallException("Mailer driver {$driverName} not exist");
            }
        }

        return $this->$driver();
    }
}
