<?php
/**
 *
 * think-mail [A powerful and beautiful php mailer for All of ThinkPHP and Other PHP Framework based Symfony Mailer]
 *
 * @author    yzh52521
 * @link      https://github.com/yzh52521/think-mail
 * @copyright 2022 yzh52521 all rights reserved.
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

namespace mailer;


/**
 * Class Config
 * @package mailer\lib
 */
class Config
{
    /**
     * @var array 配置项
     */
    private static array $config = [];
    /**
     * @var bool 是否初始化
     */
    private static bool $isInit = false;

    /**
     * 初始化配置项
     *
     * @param array $config
     */
    public static function init(array $config = [])
    {
        if ($config) {
            self::$config = array_merge(self::$config, $config);
            self::$isInit = true;
        } elseif (!self::$isInit) {
            self::detect();
            self::$isInit = true;
        }
    }

    /**
     * 获取配置参数 为空则获取所有配置
     *
     * @param string|null $name 配置参数名
     * @param mixed $default 默认值
     *
     * @return mixed
     */
    public static function get(string $name = null, $default = null)
    {
        self::init();
        if (empty($name)) {
            return self::$config;
        }
        if (isset(self::$config[$name])) {
            return self::$config[$name];
        }
        return $default;
    }

    /**
     * 设置配置参数
     *
     * @param string|array $name 配置参数名
     * @param mixed $value 配置值
     */
    public static function set($name, $value)
    {
        self::init();
        self::$config[$name] = $value;
    }

    /**
     * 自动探测配置项
     */
    private static function detect()
    {
        if (class_exists('\\think\\facade\\Config')) {
            self::$config = \think\facade\Config::get('mailer');
        } else {
            // 其他框架如果未初始化则抛出异常
            throw new InvalidArgumentException('未初始化配置项，请使用 mail\\lib\\Config::init()初始化配置项');
        }
    }
}
