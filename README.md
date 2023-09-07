<div align="center">
# Think Mail

<p>
    <a href="https://github.com/yuanzhihai/think-mail/blob/master/LICENSE"><img src="https://img.shields.io/badge/license-MIT-7389D8.svg?style=flat" ></a>
     <a href="https://styleci.io/repos/215738797">
        <img src="https://github.styleci.io/repos/215738797/shield" alt="StyleCI">
    </a>
    <a href="https://github.com/yuanzhihai/think-mail/releases" ><img src="https://img.shields.io/github/release/yuanzhihai/think-mail.svg?color=4099DE" /></a> 
    <a href="https://packagist.org/packages/yuanzhihai/think-mail"><img src="https://img.shields.io/packagist/dt/yuanzhihai/think-mail.svg?color=" /></a> 
    <a><img src="https://img.shields.io/badge/php-8.0+-59a9f8.svg?style=flat" /></a> 
</p>
</div>

**一款支持所有PHP框架的优美的邮件发送类**，ThinkPHP框架开箱即用

基于 symfony mailer 二次开发, 为 ThinkPHP系列框架量身定制, 使 ThinkPHP 支持邮件模板、纯文本、附件邮件发送以及更多邮件功能,
邮件发送简单到只需一行代码


## 安装

```
composer require yuanzhihai/think-mail
```

## 配置

在配置文件里配置如下信息, 可以配置在 `mail.php` 或 `config.php` 文件中, 但要保证能通过 `mail.host` 访问到配置信息,
内容如下:

```
return [
        'scheme'          => 'smtp',
        'host'            => '', // 服务器地址
        'username'        => '',
        'password'        => '', // 密码
        'port'            => 465, // SMTP服务器端口号,一般为25
        'options'         => [],
        'dsn'             => '',
        'debug'           => false, // 开启debug模式会直接抛出异常, 记录邮件发送日志
        'embed'           => 'embed:', // 邮件中嵌入图片元数据标记
        //默认发信人
        'from'     => [
            'address' => 'hello@example.com',
            'name'    => 'Example',
        ]
    ]
];

```

#### embed

图片内联嵌入标识，请参考 [将图片作为元数据嵌入到邮件中](#将图片作为元数据嵌入到邮件中)

## 使用

> 使用use时, ThinkPHP6 的Mailer类的命名空间是 `mailer/Mailer`

### 使用think-mailer

```
use mailer\Mailer
or 
use mailer\facade\Mailer 
```

### 创建实例

不传递任何参数表示邮件驱动使用配置文件里默认的配置

```
$mailer = new Mailer();

```

### 门面 facade 调用 （推荐）

```
use mailer\facade\Mailer;

Mailer::from('10086@qq.com')
      ->to('your-mail@domain.com')
      ->subject('纯文本测试')
      ->text('欢迎您使用think-mail')
      ->send();
```

### 设置收件人

以下几种方式任选一种

```
$mailer->to(['10086@qq.com']);
$mailer->addTo(['10086@qq.com']);
$mailer->to('10086@qq.com');
$mailer->addTo('10086@qq.com');
```

### 设置发件人

发件人邮箱地址必须和配置项里username一致

```
$mailer->from('10086@qq.com');
$mailer->from(['10086@qq.com'=>'发件人']);
or
$mailer->addFrom('10086@qq.com');
$mailer->addFrom(['10086@qq.com'=>'发件人']);
```

### 设置抄送

以下几种方式任选一种

```
$mailer->cc(['10086@qq.com']);
$mailer->addCc(['10086@qq.com']);
$mailer->cc('10086@qq.com');
$mailer->addCc('10086@qq.com');
```

### 设置暗抄送

以下几种方式任选一种

```
$mailer->bcc(['10086@qq.com']);
$mailer->addBcc(['10086@qq.com']);
$mailer->bcc('10086@qq.com');
$mailer->addBcc('10086@qq.com');
```

### 设置回复邮件地址

```
$mailer->replyTo(['10086@qq.com']);
$mailer->addReplyTo(['10086@qq.com']);

$mailer->replyTo('10086@qq.com');
$mailer->addReplyTo('10086@qq.com');
```

### 设置邮件主题

```
$mailer->subject('邮件主题');
```

### 设置邮件内容 - HTML

```
$mailer->html('<p>欢迎使用think-mailer</p>');
```

或者使用变量替换HTML内容

```
$mailer->html('<p>欢迎使用{name}</p>', ['name' => 'think-mailer']);
```

### 设置邮件内容 - 纯文本

```
$mailer->text('欢迎使用think-mailer');
```

或者使用变量替换纯文本内容

```
$mailer->text('欢迎使用{name}', ['name' => 'think-mailer']);
```

### 设置邮件内容 - 模板

ThinkPHP系列模板, 具体请看ThinkPHP各版本框架的模板怎么用, 第二个参数是要进行模板赋值的数组

```
$mailer->view('mail/register');
$mailer->view('admin@mail/register', ['account' => $account, 'name' => $name]);
```

### 将图片作为元数据嵌入到邮件中

邮件内容中包含图片的, 可以直接指定 `img` 标签的 `src` 属性为远程图片地址, 此处图片地址必须为远程图片地址,
必须为一个带域名的完整图片链接, 这似乎很麻烦, 所以你还可以将图片作为元数据嵌入到邮件中, 至于其他文件是否也可以嵌入请自己尝试

下面介绍一下 `think-mail` 如何快速简便的将图片元数据嵌入到邮件中:

#### 配置嵌入标签

嵌入元数据需要在模板赋值或者使用 `html()` 传递变量时, 给变量添加特殊的标签, 该嵌入标签默认为 `cid:`,
你可以修改配置文件中 `embed` 项, 修改为你想要的形式

#### 模板或HTML中设置变量

在模板中, 例如 ThinkPHP 全系列都是使用 `{$var}` 的形式传递变量, 假设变量为 `image_src`, 那么模板中填写 `{$image_src}`,
如果是在HTML中, 请使用 `{image_src}`, 注意如果修改过左、右定界符请使用自己定义的左右定界符

#### 传递变量参数和值

在 `html()` 和 `view()` 方法的第二个参数里, 该数组必须有一个变量, 格式为 `['cid:image_src'] => '/path/to/image.jpg']`
或者 `['cid:image_src'] => ['file_stream', 'filename','filemime']]`, 即参数数组的键名是上面配置的 `嵌入标签 + 变量名`,
但值有两种情况:

第一, 如果值为字符串, 则该值为图片的路径 (绝对路径或相对路径) 或者 有效的url地址;

第二, 如果值为数组, 数组为 `['stream','name','mime',]` 的形式, 其中 `stream` 表示图片的数据流, 即是未保存的文件数据流,
例如 `fopen()` 方法获取的文件数据流, 第二个参数为文件名, 默认为 `image`,第三个参数可选, 为文件的mime类型,
默认为 `image/jpeg`

#### 示例

```
Mailer::form('10086@qq.com')
    ->to('10086@qq.com') 
    ->subject('测试邮件模板中嵌入图片元数据')
    ->view('index@mail/index', [
        'date' => date('Y-m-d H:i:s'),     
        'cid:image' => ROOT_PATH . 'image.jpg',
        // 'cid:image' => 'https://image34.360doc.com/DownloadImg/2011/08/2222/16275597_64.jpg',
        // 'cid:image' => [fopen('/path/to/image1.jpg','r')],
        // 'cid:image' => [fopen('/path/to/image1.jpg','r'),'image','image/jpg'],
     ])
    ->send();
```

其中模板的内容如下:

```
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>测试邮件</title>
</head>
<body>
<p>尊敬的cattong:</p>
<p>     这是一封模板测试邮件</p>
<p>{$date}</p>
<p>
    <img src="{$image}" alt="">
</p>
</body>
</html>
```

在 HTML 中使用一样:

```
Mailer::form('10086@qq.com')
    ->to('10086@qq.com') 
    ->subject('测试邮件模板中嵌入图片元数据')
    ->html('<img src="{image}" />图片测试', [
        'cid:image' => '/path/to/image.jpg',
        // 'cid:image' => 'https://image34.360doc.com/DownloadImg/2011/08/2222/16275597_64.jpg',
        // 'cid:image' => [fopen('/path/to/image1.jpg','r')],
        // 'cid:image' => [fopen('/path/to/image1.jpg','r'),'image','image/jpg')],
     ])
    ->send();
```

### 添加附件

```
$mailer->attach('/path/to/file.jpg');
$mailer->attachContent(fopen('/path/to/file.jpg','r'));
```

或者指定附件的文件名

```
$mailer->attach('/path/to/file.jpg', ['fileName'=>文件名.jpg','contentType'=>'image/jpeg']);

$mailer->attachContent(fopen('/path/to/file.jpg','r'),['fileName'=>文件名.jpg','contentType'=>'image/jpeg']);
```

###设置消息加密/签名

```
消息加密
$encryptor=new SMimeEncrypter('/path/to/certificate.crt');
$mailer->withEncryptor($encryptor);  @see https://symfony.com/doc/current/mailer.html#encrypting-messages

签名

$signer = new DkimSigner('file:///path/to/private-key.key', 'example.com', 'sf');
or
$signer = new SMimeSigner('/path/to/certificate.crt', '/path/to/certificate-private-key.key');


$mailer->withSigner($signer); @see https://symfony.com/doc/current/mailer.html#signing-messages

```

### 设置字符编码

```
$mailer->charset('utf8');
```

### 设置邮件优先级

```
$mailer->priority(1);
// 可选值有: 
// 1 Highest
// 2 High
// 3 Normal
// 4 Low
// 5 Lowest
```

### 发送邮件

```
$mailer->send();
```

```
$mailer->form('10086@qq.com')
    ->to('10086@qq.com')
    ->subject('邮件主题')
    ->text('邮件内容')
    ->send();
```

如果执行过邮件发送过邮件发送之后, 需要重新初始化

```
// 第一次发送
$mailer->form('10086@qq.com')
    ->to('10086@qq.com')
    ->subject('邮件主题')
    ->text('邮件内容')
    ->send();
    
// 接着进行第二次发送
$mailer->init();
// 或者直接连贯调用
$mailer->init()->to()->...->send();
```


邮件发送失败会直接以异常抛出, 或者可以通过 getError() 获取错误信息
```
$mailer->getError();
```
使用 `getHeaders()` 和 `getHeadersString()` 方法可以获取头信息
`getHeaders()` 返回的是头信息数组, `getHeadersString()` 返回的是头信息字符串

## Issues

如果有遇到问题请提交 [issues](https://github.com/yuanzhihai/think-mail/issues)

## License

Apache 2.0


## 支持我
您的认可是我继续前行的动力,如果您觉得think-mail对您有帮助,请支持我,谢谢您!
* 方式一: 点击右上角`⭐Star`按钮
* 方式二: 扫描下方二维码,打赏我
<div style="float: left;text-align: left; width: 610px">
<img src="https://github.com/yuanzhihai/webman-task/blob/main/1631693468455_.pic.jpg" alt="扫码打赏我" width="300" /><img src="https://github.com/yuanzhihai/webman-task/blob/main/1641693468493_.pic.jpg" alt="扫码打赏我" width="300" /></div>


