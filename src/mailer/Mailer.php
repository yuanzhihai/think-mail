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

use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\HeaderInterface;

/**
 * Class Mailer
 * @package mailer
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

    /**
     * @var string|null 错误信息
     */
    protected ?string $err_msg;

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
    public function init(): self
    {
        $this->message = new Email();
        return $this;
    }

    /**
     * 获取字符编码
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * 设置字符编码
     *
     * @param string $charset
     *
     * @return $this
     */
    public function charset(string $charset): self
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
    public function subject(string $subject): self
    {
        $this->message->subject($subject);

        return $this;
    }


    /**
     * 获取邮件主题
     * @return string
     */
    public function getSubject(): string
    {
        return (string)$this->message->getSubject();
    }


    public function getDate(): ?DateTimeImmutable
    {
        return $this->message->getDate();
    }


    /**
     * 设置邮件date
     * @param DateTimeInterface $date
     * @return $this
     */
    public function date(DateTimeInterface $date): self
    {
        $this->message->date($date);
        return $this;
    }

    /**
     * 设置发件人
     *
     * @param Address|string $address
     *
     * @return $this
     */
    public function from($address): self
    {
        $this->message->from(...$this->convertStringsToAddresses($address));

        return $this;
    }

    /**
     * 获取发件人
     * @return string|string[]
     */
    public function getFrom()
    {
        return $this->convertAddressesToStrings($this->message->getFrom());
    }

    /**
     * 设置收件人
     *
     * @param Address|string $address
     *
     * @return $this
     */
    public function to($address): self
    {
        $this->message->to(...$this->convertStringsToAddresses($address));

        return $this;
    }

    /** 获取收件人
     * @return string|string[]
     */
    public function getTo()
    {
        return $this->convertAddressesToStrings($this->message->getTo());
    }

    /**
     * 设置抄送人
     * @param Address|string $address
     * @return $this
     */
    public function cc($address): self
    {
        $this->message->cc(...$this->convertStringsToAddresses($address));

        return $this;
    }

    /**
     * 获取抄送人
     * @return string|string[]
     */
    public function getCc()
    {
        return $this->convertAddressesToStrings($this->message->getCc());
    }

    /**
     * 设置暗抄人
     * @param Address|string $address
     * @return $this
     */
    public function bcc($address): self
    {
        $this->message->bcc(...$this->convertStringsToAddresses($address));

        return $this;
    }

    /**
     * 获取暗抄人
     * @return string|string[]
     */
    public function getBcc()
    {
        return $this->convertAddressesToStrings($this->message->getBcc());
    }

    /**
     * 获取邮件HTML内容
     * @return string
     */
    public function getHtmlBody(): string
    {
        return (string)$this->message->getHtmlBody();
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function addHeader($name, $value): self
    {
        $this->message->getHeaders()->addTextHeader($name, $value);
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function header($name, $value): self
    {
        $headers = $this->message->getHeaders();

        if ($headers->has($name)) {
            $headers->remove($name);
        }

        foreach ((array)$value as $v) {
            $headers->addTextHeader($name, $v);
        }

        return $this;
    }

    /**
     *
     * @param array $headers
     * @return $this
     */
    public function headers(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->header($name, $value);
        }

        return $this;
    }

    /**
     * 设置邮件内容为HTML内容
     *
     * @param resource|string|null $content
     *
     * @return $this
     */
    public function html(string $content, $param, $config): self
    {
        if ($param) {
            $content = strtr($content, $this->parseParam($param, $config));
        }
        $this->message->html($content, $this->charset);

        return $this;
    }

    public function getTextBody(): string
    {
        return (string)$this->message->getTextBody();
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
    public function text(string $content, $param, $config): self
    {
        if ($param) {
            $content = strtr($content, $this->parseParam($param, $config));
        }
        $this->message->text($content, $this->charset);

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
    public function attach(string $filePath, array $options = []): self
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
    public function attachContent($content, array $options = []): self
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
    public function priority(int $priority = 1): self
    {
        $this->message->priority($priority);

        return $this;
    }

    public function getPriority(): int
    {
        return $this->message->getPriority();
    }

    /**
     * 设置回复邮件
     * @param Address|string $address
     * @return $this
     */
    public function replyTo($address): self
    {
        $this->message->replyTo($address);

        return $this;
    }

    public function getReplyTo()
    {
        return $this->convertAddressesToStrings($this->message->getReplyTo());
    }

    public function getReturnPath(): string
    {
        $returnPath = $this->message->getReturnPath();
        return $returnPath === null ? '' : $returnPath->getAddress();
    }

    /**
     *
     * @param string $address
     * @return $this
     */
    public function returnPath(string $address): self
    {
        $this->message->returnPath($address);
        return $this;
    }

    public function getSender(): string
    {
        $sender = $this->message->getSender();
        return $sender === null ? '' : $sender->getAddress();
    }

    /**
     * @param string $address
     * @return $this
     */
    public function sender(string $address): self
    {
        $this->message->sender($address);
        return $this;
    }


    /**
     * 获取头信息
     *
     */
    public function getHeaders($name): array
    {
        $headers = $this->message->getHeaders();
        if (!$headers->has($name)) {
            return [];
        }

        $values = [];

        /** @var HeaderInterface $header */
        foreach ($headers->all($name) as $header) {
            $values[] = $header->getBodyAsString();
        }

        return $values;
    }

    /**
     * 获取头信息 (字符串)
     *
     * @return string
     */
    public function getHeadersString(): string
    {
        return $this->message->getHeaders()->toString();
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
     * @param array $transport
     * @param \Closure|null $send
     * @return bool
     * @throws \Exception
     */
    public function send($message = null, array $transport = [], \Closure $send = null)
    {
        try {
            // 匿名函数
            if ($message instanceof \Closure) {
                call_user_func_array($message, [&$this, &$this->message]);
            }
            if ($transport instanceof TransportInterface) {
                $mailer = $transport;
            } else {
                $transportInstance = new Transport();
                $transportInstance->setTransport($transport);
                $mailer = $transportInstance->getSymfonyMailer();
            }

            if (Config::get('debug')) {
                Log::write(var_export($this->getHeadersString(), true), Log::INFO);
            }
            // 发送邮件
            if ($send instanceof \Closure) {
                call_user_func_array($send, [$mailer, $this]);
            } else {
                $mailer->send($this->message);
            }
            return true;
        } catch (TransportExceptionInterface $e) {
            $this->err_msg = $e->getMessage();
            Log::write($e->getMessage(), Log::ERROR);
            if (Config::get('debug')) {
                // 调试模式直接抛出异常
                throw new Exception($e->getMessage());
            }
            return false;
        } catch (Exception $e) {
            $this->err_msg = $e->getMessage();
            if (Config::get('debug')) {
                // 调试模式直接抛出异常
                throw new Exception($e->getMessage());
            }
            return false;
        }
    }


    /**
     * 获取错误信息
     *
     * @return string|null
     */
    public function getError()
    {
        return $this->err_msg;
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
        $flag = Config::get('embed', 'cid:');
        if (false !== strpos($k, $flag)) {
            if (is_array($v) && $v) {
                if (!isset($v[1])) {
                    $v[1] = 'image.jpg';
                }
                if (!isset($v[2])) {
                    $v[2] = 'image/jpeg';
                }
                [$imgData, $name, $mime] = $v;
                $v = $this->message->embedFromPath($imgData, $name, $mime);
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
