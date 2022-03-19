## Think Mail
**一款支持所有PHP框架的优美的邮件发送类**，ThinkPHP框架【6.0.x】开箱即用，其他框架初始化配置即可使用

基于 symfony mailer 二次开发, 为 ThinkPHP系列框架量身定制, 使 ThinkPHP 支持邮件模板、纯文本、附件邮件发送以及更多邮件功能, 邮件发送简单到只需一行代码

同时了方便其他框架或者非框架使用, think-mail也非常容易拓展融合到其他框架中, 欢迎大家 `Fork` 和 `Star`, 提交代码让think-mail支持更多框架


## 优雅的发送邮件
**ThinkPHP6.0.x 示例**
```
use mailer\think\Mailer;

$mailer = Mailer::instance();
$mailer->from('10086@qq.com@qq.com')
      ->to('your-mail@domain.com')
      ->subject('纯文本测试')
      ->text('欢迎您使用think-mail')
      ->send();


## 安装
```
composer require yzh52521/think-mail

## 配置
在配置文件里配置如下信息, 可以配置在 `mail.php` 或 `config.php` 文件中, 但要保证能通过 `mail.drive`, `mail.host` 访问到配置信息, 内容如下:
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
        'left_delimiter'  => '{', // 模板变量替换左定界符, 可选, 默认为 {
        'right_delimiter' => '}', // 模板变量替换右定界符, 可选, 默认为 }
        'log_drive'       => '', // 日志驱动类, 可选, 如果启用必须实现静态 public static function write($content, $level = 'debug') 方法
        'log_path'        => '', // 日志路径, 可选, 不配置日志驱动时启用默认日志驱动, 默认路径是 /path/to/tp-mailer/log, 要保证该目录有可写权限, 最好配置自己的日志路径
        'embed'           => 'embed:', // 邮件中嵌入图片元数据标记
];


public static function write($content, $level = 'debug')
{
    echo '日志内容：' . $content;
    echo '日志级别：' . $level;
}
```

#### log_path
日志驱动为默认是日志存储路径，不配置默认为 `think-mail/log/`，例如可配置为 `ROOT_PATH . 'runtime/log/'`

#### embed
图片内联嵌入标识，请参考 [将图片作为元数据嵌入到邮件中](#将图片作为元数据嵌入到邮件中)

## 使用
> 使用use时, ThinkPHP6 的Mailer类的命名空间是 `mailer/think/Mailer`


### 使用think-mailer
```
use mailer\think\Mailer
```

### 创建实例
不传递任何参数表示邮件驱动使用配置文件里默认的配置
```
$mailer = Mailer::instance();
```

### 设置收件人
以下几种方式任选一种
```
$mailer->to(['10086@qq.com']);
$mailer->to(['10086@qq.com']);
$mailer->to('10086@qq.com');
$mailer->to(['tianpian0805@qq.com', '10086@qq.com']);
$mailer->to(['tianpian0805@qq.com', '10086@qq.com', 'tianpian0805@163.com']);
```

### 设置发件人
发件人邮箱地址必须和配置项里一致, 默认会自动设置发件地址 (配置里的addr) 和发件人 (配置里的name)
```
$mailer->from('10086@qq.com');
$mailer->from(['10086@qq.com']);
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
邮件内容中包含图片的, 可以直接指定 `img` 标签的 `src` 属性为远程图片地址, 此处图片地址必须为远程图片地址, 必须为一个带域名的完整图片链接, 这似乎很麻烦, 所以你还可以将图片作为元数据嵌入到邮件中, 至于其他文件是否也可以嵌入请自己尝试

下面介绍一下 `think-mail` 如何快速简便的将图片元数据嵌入到邮件中:

#### 配置嵌入标签
嵌入元数据需要在模板赋值或者使用 `html()` 传递变量时, 给变量添加特殊的标签, 该嵌入标签默认为 `cid:`, 你可以修改配置文件中 `cid` 项, 修改为你想要的形式

#### 模板或HTML中设置变量
在模板中, 例如 ThinkPHP 全系列都是使用 `{$var}` 的形式传递变量, 假设变量为 `image_src`, 那么模板中填写 `{$image_src}`, 如果是在HTML中, 请使用 `{image_src}`, 注意如果修改过左、右定界符请使用自己定义的左右定界符

#### 传递变量参数和值
在 `html()` 和 `view()` 方法的第二个参数里, 该数组必须有一个变量, 格式为 `['cid:image_src'] => '/path/to/image.jpg']` 或者 `['cid:image_src'] => ['file_stream', 'filename','filemime']]`, 即参数数组的键名是上面配置的 `嵌入标签 + 变量名`, 但值有两种情况:

第一, 如果值为字符串, 则该值为图片的路径 (绝对路径或相对路径) 或者 有效的url地址;

第二, 如果值为数组, 数组为 `['stream','name','mime',]` 的形式, 其中 `stream` 表示图片的数据流, 即是未保存的文件数据流, 例如 `file_get_contents()` 方法获取的文件数据流, 第二个参数为文件名, 默认为 `image.jpg`,第二个参数可选, 为文件的mime类型, 默认为 `image/jpeg`

#### 示例
```
Mailer::instance()
    ->form('10086@qq.com')
    ->to('10086@qq.com') 
    ->subject('测试邮件模板中嵌入图片元数据')
    ->view('index@mail/index', [
        'date' => date('Y-m-d H:i:s'),     
        'embed:image' => ROOT_PATH . 'image.jpg',
        // 'cid:image' => 'http://image34.360doc.com/DownloadImg/2011/08/2222/16275597_64.jpg',
        // 'cid:image' => [file_get_contents(ROOT_PATH . 'image1.jpg')],
        // 'cid:image' => [file_get_contents(ROOT_PATH . 'image1.jpg', 'image/png', '图片.png')],
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
Mailer::instance()
    ->form('10086@qq.com')
    ->to('10086@qq.com') 
    ->subject('测试邮件模板中嵌入图片元数据')
    ->html('<img src="{image}" />图片测试', [
        'cid:image' => ROOT_PATH . 'image.jpg',
        // 'cid:image' => 'http://image34.360doc.com/DownloadImg/2011/08/2222/16275597_64.jpg',
        // 'cid:image' => [file_get_contents(ROOT_PATH . 'image1.jpg')],
        // 'cid:image' => [file_get_contents(ROOT_PATH . 'image1.jpg', 'image/png', '图片.png')],
     ])
    ->send();
```


### 添加附件
```
$mailer->attach('http://domain.com/path/to/file.jpg');
```

或者指定附件的文件名
```
$mailer->attach(ROOT_PATH . 'foo.jpg', ['name'=>文件名.jpg','contentType'=>'image/jpeg']);
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

开启 `debug` 模式后, 邮件发送失败会直接以异常抛出, 如果没有开启, 可以通过 `getError()` 获取错误信息
```
$mailer->getError();
```

使用 `getHeaders()` 和 `getHeadersString()` 方法可以获取头信息
`getHeaders()` 返回的是头信息数组, `getHeadersString()` 返回的是头信息字符串


## 动态配置
`mailer/lib/Config` 可以进行邮件动态配置，可以读取配置或者重新设置默认配置项，也可以用于其他非 ThinkPHP 框架进行配置项初始化
```
class Config
{
    /**
     * 初始化配置项
     *
     * @param array $config 请参考配置项里的配置格式，其他非ThinkPHP框架不支持自动探测自动初始化配置项，务必使用该方法初始化配置项
     */
    public static function init($config = [])
    {
    }

    /**
     * 获取配置参数 为空则获取所有配置
     *
     * @param string $name    配置参数名
     * @param mixed  $default 默认值
     *
     * @return mixed
     */
    public static function get($name = null, $default = null)
    {
    }

    /**
     * 设置配置参数
     *
     * @param string|array $name  配置参数名
     * @param mixed        $value 配置值
     */
    public static function set($name, $value)
    {
    }
}

```

### 第一步: 初始化配置项
使用 `mailer\lib\Config` 的 `init()` 方法初始化配置项，例如：
```
use mailer\lib\Config

// 配置格式参见前面的配置
$config = [
    'scheme' => 'smtp',
    'host'   => 'smtp.qq.com',
    ...
    ];

Config::init($config);
```

### 第二步: 实现 `$mailer->view()` 方法
写自己的类继承 `mailer\lib\Mailer`  然后实现里面的 `view` 方法, 根据自己的框架渲染出自己的模板，如果不需要使用 `view()` 方法可以忽略这一步，直接进入下一步:
```
/**
 * 载入一个模板作为邮件内容
 *
 * @param string $template
 * @param array  $param
 * @param array  $config
 *
 * @return Mailer
 */
public function view($template, $param = [], $config = [])
{
    $view = View::instance(ThinkConfig::get('template'), ThinkConfig::get('view_replace_str'));
    // 处理变量中包含有对元数据嵌入的变量
    foreach ($param as $k => $v) {
        $this->embedImage($k, $v, $param);
    }
    $content = $view->fetch($template, $param, [], $config);

    return $this->html($content,'','');
}
```

## Issues
如果有遇到问题请提交 [issues](https://github.com/yzh52521/think-mail/issues)


## License
Apache 2.0
