<?php
/**
 * tp-mailer [A powerful and beautiful php mailer for All of ThinkPHP and Other PHP Framework based SwiftMailer]
 *
 * @author    yuan1994 <tianpian0805@gmail.com>
 * @link      https://github.com/yuan1994/tp-mailer
 * @copyright 2016 yuan1994 all rights reserved.
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
            $path = dirname($_SERVER['DOCUMENT_ROOT']) . '/runtime/log/think-mail' . DIRECTORY_SEPARATOR;
        }
        $destination = $path . '/mailer-' . date('Y-m-d') . '.log';
        // 自动创建日志目录
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        if (PHP_SAPI == 'cli') {
            $remote = '';
            $url    = '';
        } else {
            $remote = $_SERVER["REMOTE_ADDR"] ? $_SERVER["REMOTE_ADDR"] : '127.0.0.1';
            $url    = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : '/';
        }
        $content = '[ ' . $level . ' ] ' . $content;
        error_log("[{$now}] " . $remote . ' ' . $url . "\r\n{$content}\r\n", 3, $destination);
    }
}
