<?php
/**
 * think-mail [A powerful and beautiful php mailer for All of ThinkPHP and Other PHP Framework based Symfony Mailer]
 *
 * @author    yzh52521
 * @link      https://github.com/yzh52521/think-mail
 * @copyright 2022 yzh52521 all rights reserved.
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

namespace mailer\log;

use mailer\Config;

class File
{
    const DEBUG = 'DEBUG';

    /**
     * 写入日志
     *
     * @param $content
     * @param string $level
     */
    public static function write($content, string $level = self::DEBUG)
    {
        $now  = date(' c ');
        $path = Config::get('log_path');
        if (empty($path)) {
            $path = './runtime/log/think-mail' . DIRECTORY_SEPARATOR;
        }
        $destination = $path . '/mail-' . date('Y-m-d') . '.log';
        // 自动创建日志目录
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $content = '[ ' . $level . ' ] ' . $content;
        error_log("[{$now}] \r\n{$content}\r\n", 3, $destination);
    }
}
