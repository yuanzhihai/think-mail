<?php
/**
 * think-mail [A powerful and beautiful php mailer for All of ThinkPHP and Other PHP Framework based SwiftMailer]
 *
 * @author    yzh52521
 * @link      https://github.com/yzh52521/think-mail
 * @copyright 2019 yzh52521 all rights reserved.
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */
namespace mailer\lib\log;

use mailer\lib\Config;

class File
{
    const DEBUG = 'debug';
    const INFO = 'info';

    /**
     * 写入日志
     *
     * @param $content
     * @param string $level
     */
    public static function write($content, $level = self::DEBUG)
    {
        $now  = date(' c ');
        $path = Config::get('log_path');
        if (empty($path)) {
            $path =  './runtime/log/think-mail' . DIRECTORY_SEPARATOR;
        }
        $destination = $path . '/mailer-' . date('Y-m-d') . '.log';
        // 自动创建日志目录
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        if (PHP_SAPI === 'cli') {
            $remote = '';
            $url    = '';
        } else {
            $remote = $_SERVER["REMOTE_ADDR"] ? $_SERVER["REMOTE_ADDR"] : '0.0.0.0';
            $url    = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : '/';
        }
        $content = '[ ' . $level . ' ] ' . $content;
        error_log("[{$now}] " . $remote . ' ' . $url . "\r\n{$content}\r\n", 3, $destination);
    }
}
