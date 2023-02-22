<?php

/**
 * 配置文件
 */
return [
    'scheme'          => 'smtp',// "smtps": using TLS, "smtp": without using TLS.
    'host'            => '', // 服务器地址
    'username'        => '',
    'password'        => '', // 密码
    'port'            => 465, // SMTP服务器端口号,一般为25
    'options'         => [], // See: https://symfony.com/doc/current/mailer.html#tls-peer-verification
     // 'dsn'             => '',
    'embed'           => 'cid:', // 邮件中嵌入图片元数据标记
];
