<?php
/**
 * think-mail [A powerful and beautiful php mailer for All of ThinkPHP and Other PHP Framework based Symfony Mailer]
 *
 * @author    yuanzhihai
 * @link      https://github.com/yuanzhihai/think-mail
 * @copyright 2022 yuanzhihai all rights reserved.
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

declare(strict_types=1);

namespace mailer;

use DateTimeImmutable;
use DateTimeInterface;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\HeaderInterface;
use think\facade\Config;
use think\facade\Log;
use think\facade\View;

/**
 * Class Mailer
 * @package mailer
 */
class Mailer implements MessageWrapperInterface
{

    private string $charset = 'utf-8';
    /**
     * @var Email
     */
    protected Email $message;

    /**
     * @var string|null 错误信息
     */
    protected ?string $errMsg;

    /** @var array|string 发信人 */
    protected string|array $from = [];
    protected $html;


    private ?MessageEncrypterInterface $encrypter = null;

    /**
     * @see https://symfony.com/doc/current/mailer.html#signing-messages
     */
    public ?MessageSignerInterface $signer = null;

    public array $signerOptions = [];

    private mixed $transport;


    public function __construct($transport = [])
    {
        $config          = config('mailer');
        $this->transport = $transport;
        $this->from      = [$config['from']['address'] => $config['from']['name']];
        $this->init();
    }

    public function __clone()
    {
        $this->message = clone $this->message;
    }

    public function __sleep(): array
    {
        return ['email', 'charset'];
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


    /**
     * @return DateTimeImmutable|null
     */
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
     * @param array|string $address
     *
     * @return $this
     */
    public function from(array|string $address): self
    {
        $this->from = $address;
        return $this;
    }

    /**
     * 增加发件人
     * @param array|string $address
     * @return $this
     */
    public function addFrom(array|string $address): self
    {
        $this->from = $address;

        return $this;
    }

    /**
     * 设置 发件人
     * @return $this
     */
    protected function buildFrom()
    {
        if (!empty($this->from)) {
            $this->message->from(...$this->convertStringsToAddresses($this->from));
        }
    }

    /**
     * 获取发件人
     * @return array|string
     */
    public function getFrom(): array|string
    {
        return $this->convertAddressesToStrings($this->message->getFrom());
    }

    /**
     * 设置收件人
     *
     * @param array|string $address
     *
     * @return $this
     */
    public function to(array|string $address): self
    {
        $this->message->to(...$this->convertStringsToAddresses($address));

        return $this;
    }

    /**
     * 增加收件人
     * @param array|string $address
     * @return $this
     */
    public function addTo(array|string $address): self
    {
        $this->message->addTo(...$this->convertStringsToAddresses($address));

        return $this;
    }

    /** 获取收件人
     * @return string|array
     */
    public function getTo(): array|string
    {
        return $this->convertAddressesToStrings($this->message->getTo());
    }

    /**
     * 设置抄送人
     * @param array|string $address
     * @return $this
     */
    public function cc(array|string $address): self
    {
        $this->message->cc(...$this->convertStringsToAddresses($address));

        return $this;
    }

    /**
     * 增加抄送人
     * @param array|string $address
     * @return $this
     */
    public function addCc(array|string $address): self
    {
        $this->message->addCc(...$this->convertStringsToAddresses($address));

        return $this;
    }

    /**
     * 获取抄送人
     * @return string|array
     */
    public function getCc(): array|string
    {
        return $this->convertAddressesToStrings($this->message->getCc());
    }

    /**
     * 设置暗抄人
     * @param array|string $address
     * @return $this
     */
    public function bcc(array|string $address): self
    {
        $this->message->bcc(...$this->convertStringsToAddresses($address));

        return $this;
    }

    /**
     * 增加暗抄人
     * @param array|string $address
     * @return $this
     */
    public function addBcc(array|string $address): self
    {
        $this->message->addBcc(...$this->convertStringsToAddresses($address));

        return $this;
    }

    /**
     * 获取暗抄人
     * @return array|string
     */
    public function getBcc(): array|string
    {
        return $this->convertAddressesToStrings($this->message->getBcc());
    }

    /**
     * 获取邮件HTML内容
     * @return string
     */
    #[Pure]
    public function getHtmlBody(): string
    {
        return (string)$this->message->getHtmlBody();
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function addHeader(string $name, string $value): self
    {
        $this->message->getHeaders()->addTextHeader($name, $value);

        return $this;
    }

    /**
     * @param string $name
     * @param $value
     * @return $this
     */
    public function header(string $name, $value): self
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
     * @param string $content
     * @param array $param
     * @param array $config
     * @return $this
     */
    public function html(string $content, array $param = [], array $config = []): self
    {
        $this->html = $content;

        if ($param) {
            $content = strtr($content, $this->parseParam($param, $config));
        }
        $this->message->html($content, $this->charset);

        return $this;
    }

    /**
     * @return string
     */
    #[Pure]
    public function getTextBody(): string
    {
        return (string)$this->message->getTextBody();
    }

    /**
     * 设置邮件内容为纯文本内容
     *
     * @param string $content
     * @param array $param
     * @param array $config
     *
     * @return $this
     */
    public function text(string $content, array $param = [], array $config = []): self
    {
        if ($param) {
            $content = strtr($content, $this->parseParam($param, $config));
        }
        $this->message->text($content, $this->charset);

        return $this;
    }

    /**
     * 设置模板
     * @param string $template
     * @param array $param
     * @return $this
     */
    public function view(string $template, array $param = [])
    {
        // 处理变量中包含有对元数据嵌入的变量
        foreach ($param as $k => $v) {
            $this->embedImage($k, $v, $param);
        }
        $content = View::fetch($template, $param);
        return $this->html($content);
    }

    /**
     * 获取邮件内容
     * @return string
     */
    public function render(): string
    {
        return $this->html ?: $this->getTextBody();
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
     * @param array|string $address
     * @return $this
     */
    public function replyTo(array|string $address): self
    {
        $this->message->replyTo(...$this->convertStringsToAddresses($address));

        return $this;
    }

    /**
     * 增加回复邮件地址
     * @param array|string $address
     * @return $this
     */
    public function addReplyTo(array|string $address): self
    {
        $this->message->addReplyTo(...$this->convertStringsToAddresses($address));

        return $this;
    }


    public function getReplyTo(): array|string
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
     * @return array
     */
    protected function parseParam(array $param, array $config = [])
    {
        $ret   = [];
        $left  = $config['left'] ?: Config::get('view.tpl_begin', '{');
        $right = $config['right'] ?: Config::get('view.tpl_end', '}');
        foreach ($param as $k => $v) {
            // 处理变量中包含有对元数据嵌入的变量
            $this->embedImage($k, $v, $param);
            $ret[$left . $k . $right] = $v;
        }

        return $ret;
    }


    /**
     * Returns a Symfony message instance.
     *
     * @return Email Symfony message instance.
     */
    public function getSymfonyMessage(): Email
    {
        return $this->message;
    }

    /**
     * 发送邮件
     * @param \Closure|null $message
     * @param array $transport
     * @return bool
     */
    public function send(\Closure $message = null, array $transport = []): bool
    {
        try {
            // 匿名函数
            if ($message instanceof \Closure) {
                call_user_func_array($message, [&$this, &$this->message]);
            }

            if (empty($transport) && $this->transport) {
                $transport = $this->transport;
            }

            $transportInstance = new Transport();
            $transportInstance->setTransport($transport);
            $mailer = $transportInstance->getSymfonyMailer();

            if (!($this instanceof MessageWrapperInterface)) {
                throw new InvalidArgumentException(sprintf(
                    'The message must be an instance of "%s". The "%s" instance is received.',
                    MessageWrapperInterface::class,
                    get_class($this),
                ));
            }

            $message = $this->getSymfonyMessage();

            if ($this->encrypter !== null) {
                $message = $this->encrypter->encrypt($message);
            }

            if ($this->signer !== null) {
                $message = $this->signer->sign($message, $this->signerOptions);
            }

            $this->buildFrom();
            // 发送邮件
            $mailer->send($message);
            return true;
        } catch (TransportExceptionInterface|\Throwable $e) {
            $this->errMsg = $e->getMessage();
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }


    /**
     * 获取错误信息
     */
    public function getError(): ?string
    {
        return $this->errMsg;
    }


    /**
     * 对嵌入元数据的变量进行处理
     *
     * @param string $k
     * @param array|string $v
     * @param array $param
     */
    protected function embedImage(string &$k, array|string &$v, array &$param)
    {
        $flag = Config::get('mailer.embed', 'cid:');
        if (str_contains($k, $flag)) {
            $name = 'image';
            if (is_array($v) && $v) {
                if (!isset($v[1])) {
                    $v[1] = $name;
                }
                if (!isset($v[2])) {
                    $v[2] = null;
                }
                [$img, $name, $mime] = $v;
                $this->message->embed($img, $name, $mime);
            } else {
                $this->message->embedFromPath($v, $name);
            }
            unset($param[$k]);
            $k         = substr($k, strlen($flag));
            $param[$k] = $flag . $name;
        }
    }

    /**
     * Converts address instances to their string representations.
     *
     * @param Address[] $addresses
     *
     * @return array<string, string>|string
     */
    #[Pure]
    private function convertAddressesToStrings(array $addresses): array|string
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
     * @param string|array<int|string, string> $strings
     *
     * @return Address[]
     */
    private function convertStringsToAddresses(array|string $strings): array
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
