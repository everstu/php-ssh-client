# PHP SSH Client
这是一个php实现SSH连接远程linux服务器的帮助类。

依赖php扩展php_ssh2

扩展下载地址：http://pecl.php.net/package/ssh2

demo:
composer install wengheng/php-ssh-client
```php
<?php
use SSH_Client;
$host = '101.0.0.1';//ssh host
$port = 22;//ssh端口
$username = 'root';//登录用户名
/*
当$auth_mode为pwd时传入 登录密码，
登录模式为crt时 传入数组
[
'pub_cert'=>'./public_key.crt',
'priv_cert'=>'./private_key.crt',
'cert_pwd'=>''
]
证书路径:pub_cert为公钥，priv_cert为私钥，cert_pwd为证书密码。
*/
$auth_info = '123456';//登录信息见上面说明
$auth_mode = 'pwd';//登录模式 证书登录使用crt 默认使用pwd 密码登录

$ssh_client = new SSH_Client\Client($host, $username, $auth_info, $port, $auth_mode);
$cmd = 'ls';

$res = $ssh_client->exec($cmd);
```