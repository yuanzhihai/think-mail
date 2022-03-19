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

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Class Mailer
 * @package mailer\lib
 * @method Mailer view(string $template, array $param = [], array $config = [])
 */
class Mailer
{
    /*
     * @var Mailer 单例
     */
    protected static $instance;

    private string $charset = 'utf-8';
    /**
     * @var Email
     */
    protected Email $message;


    protected $transport;

    /**
     *
     * @return Mailer
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new static();
        }
        return self::$instance;
    }


    public function __construct()
    {
        $this->init();
    }

    /**
     * 重置实例
     *
     * @return $this
     */
    public function init()
    {
        $this->message = new Email();
        return $this;
    }

    /**
     * 设置字符编码
     *
     * @param string $charset
     *
     * @return $this
     */
    public function charset(string $charset)
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * 设置邮件主题
     *
     * @param string $subject
     *
     * @return $this
     */
    public function subject(string $subject)
    {
        $this->message->subject($subject);

        return $this;
    }

    /**
     * 设置发件人
     *
     * @param Address|string $address
     *
     * @return $this
     */
    public function from($address)
    {
        $this->message->from(...$this->convertStringsToAddresses($address));

        return $this;
    }

    /**
     * 设置收件人
     *
     * @param Address|string $address
     *
     * @return $this
     */
    public function to($address)
    {
        $this->message->to(...$this->convertStringsToAddresses($address));

        return $this;
    }

    /**
     * 设置抄送人
     * @param Address|string $address
     * @return $this
     */
    public function cc($address)
    {
        $this->message->cc(...$this->convertStringsToAddresses($address));

        return $this;
    }

    /**
     * 设置暗抄人
     * @param Address|string $address
     * @return $this
     */
    public function bcc($address)
    {
        $this->message->bcc(...$this->convertStringsToAddresses($address));

        return $this;
    }

    /**
     * 设置邮件内容为HTML内容
     *
     * @param resource|string|null $content
     *
     * @return $this
     */
    public function html(string $content, $param, $config)
    {
        if ($param) {
            $content = strtr($content, $this->parseParam($param, $config));
        }
        $this->message->html($content, $this->charset);

        return $this;
    }

    /**
     * 设置邮件内容为纯文本内容
     *
     * @param string $content
     * @param $param
     * @param $config
     *
     * @return $this
     */
    public function text(string $content, $param, $config)
    {
        if ($param) {
            $content = strtr($content, $this->parseParam($param, $config));
        }
        $this->message->text($content, $this->charset);

        return $this;
    }

    /**
     * 设置邮件内容为纯文本内容
     *
     * @param string $content
     * @param $param
     * @param $config
     *
     * @return $this
     */
    public function raw(string $content, $param, $config)
    {
        $this->text($content, $param, $config);

        return $this;
    }


    /**
     * 添加附件
     *
     * @param string $filePath
     * @param array $options
     *
     * @return $this
     */
    public function attach(string $filePath, array $options = [])
    {
        $file = [];
        if (!empty($options['fileName'])) {
            $file['name'] = $options['fileName'];
        } else {
            $file['name'] = $filePath;
        }
        if (!empty($options['contentType'])) {
            $file['contentType'] = $options['contentType'];
        } else {
            $file['contentType'] = mime_content_type($filePath);
        }
        $this->message->attachFromPath($filePath, $file['name'], $file['contentType']);

        return $this;
    }

    /**
     * @param $content
     * @param array $options
     *
     * @return $this
     */
    public function attachContent($content, array $options = [])
    {
        $file = [];
        if (!empty($options['fileName'])) {
            $file['name'] = $options['fileName'];
        } else {
            $file['name'] = null;
        }

        if (!empty($options['contentType'])) {
            $file['contentType'] = $options['contentType'];
        } else {
            $file['contentType'] = null;
        }

        $this->message->attach($content, $file['name'], $file['contentType']);
        return $this;
    }

    /**
     * 设置优先级
     *
     * @param int $priority
     *
     * @return $this
     */
    public function priority(int $priority = 1)
    {
        $this->message->priority($priority);

        return $this;
    }

    /**
     * 设置回复邮件
     * @param Address|string $address
     * @return $this
     */
    public function replyTo($address)
    {
        $this->message->replyTo($address);

        return $this;
    }


    /**
     * 获取头信息
     *
     * @return
     */
    public function getHeaders()
    {
        return $this->message->getHeaders();
    }

    /**
     * 获取头信息 (字符串)
     *
     * @return string
     */
    public function getHeadersString(): string
    {
        return $this->getHeaders()->toString();
    }

    /**
     * 将参数中的key值替换为可替换符号
     *
     * @param array $param
     * @param array $config
     *
     * @return mixed
     */
    protected function parseParam(array $param, array $config = [])
    {
        $ret            = [];
        $leftDelimiter  = $config['left_delimiter'] ?: Config::get('left_delimiter', '{');
        $rightDelimiter = $config['right_delimiter'] ?: Config::get('right_delimiter', '}');
        foreach ($param as $k => $v) {
            // 处理变量中包含有对元数据嵌入的变量
            $this->embedImage($k, $v, $param);
            $ret[$leftDelimiter . $k . $rightDelimiter] = $v;
        }

        return $ret;
    }

    /**
     * 发送邮件
     * @param null $message
     * @param \Closure|null $send
     * @throws Exception
     */
    public function send($message = null, \Closure $send = null)
    {
        try {
            // 匿名函数
            if ($message instanceof \Closure) {
                call_user_func_array($message, [&$this, &$this->message]);
            }
            $transportInstance = new Transport();
            $transportDriver   = $transportInstance->getTransport();

            $mailer = new \Symfony\Component\Mailer\Mailer($transportDriver);

            // debug模式记录日志
            if (Config::get('debug')) {
                Log::write(var_export($this->getHeadersString(), true), Log::INFO);
            }
            // 发送邮件
            if ($send instanceof \Closure) {
                call_user_func_array($send, [$mailer, $this]);
            } else {
                $mailer->send($this->message);
            }
        } catch (TransportExceptionInterface $e) {
            if (Config::get('debug')) {
                // 将错误信息记录在日志中
                $log = "Error: " . $e->getMessage() . "\n"
                    . '邮件头信息：' . "\n"
                    . var_export($this->getHeadersString(), true);
                Log::write($log, Log::ERROR);
            }
            throw new Exception($e->getMessage());
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    /**
     * 对嵌入元数据的变量进行处理
     *
     * @param string $k
     * @param string $v
     * @param array $param
     */
    protected function embedImage(string &$k, string &$v, array &$param)
    {
        $flag = Config::get('embed', 'embed:');
        if (false !== strpos($k, $flag)) {
            if (is_array($v) && $v) {
                if (!isset($v[1])) {
                    $v[1] = 'image/jpeg';
                }
                if (!isset($v[2])) {
                    $v[2] = 'image.jpg';
                }
                [$imgData, $name, $mime] = $v;
                $v = $this->message->embedFromPath($imgData, $name, $mime);
            } else {
                $v = $this->message->embedFromPath($v);
            }
            unset($param[$k]);
            $k         = substr($k, strlen($flag));
            $param[$k] = $v;
        }
    }

    /**
     * Converts address instances to their string representations.
     *
     * @param Address[] $addresses
     *
     * @return array<string, string>|string
     */
    private function convertAddressesToStrings(array $addresses)
    {
        $strings = [];

        foreach ($addresses as $address) {
            $strings[$address->getAddress()] = $address->getName();
        }

        return empty($strings) ? '' : $strings;
    }

    /**
     * Converts string representations of address to their instances.
     *
     * @param array<int|string, string>|string $strings
     *
     * @return Address[]
     */
    private function convertStringsToAddresses($strings): array
    {
        if (is_string($strings)) {
            return [new Address($strings)];
        }

        $addresses = [];

        foreach ($strings as $address => $name) {
            if (!is_string($address)) {
                // email address without name
                $addresses[] = new Address($name);
                continue;
            }

            $addresses[] = new Address($address, $name);
        }

        return $addresses;
    }

}
