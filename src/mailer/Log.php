<?php
/**
 * think-mail [A powerful and beautiful php mailer for All of ThinkPHP and Other PHP Framework based Symfony Mailer]
 *
 * @author    yzh52521
 * @link      https://github.com/yzh52521/think-mail
 * @copyright 2022 yzh52521 all rights reserved.
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

namespace mailer;

/**
 * Class Log
 * @package mailer\lib
 */
class Log
{
    const DEBUG = 'DEBUG';
    const INFO = 'INFO';
    const ERROR = 'ERROR';

    /**
     * @var object 日志驱动
     */
    private static $driver;


    public static function init()
    {
        if (null === self::$driver) {
            if (Config::get('log_drive')) {
                $driver       = Config::get('log_drive');
                self::$driver = $driver;
            } else {
                self::$driver = \mailer\log\File::class;
            }
        }
    }

    /**
     * 写入日志
     * @param $message
     * @param string $level
     * @param array $context
     */
    public static function write($message, string $level = self::DEBUG, array $context = [])
    {
        self::init();
        $driver = self::$driver;
        $driver::write($message, $level, $context);
    }
}
