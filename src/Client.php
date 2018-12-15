<?php

namespace SSH_Client;

class Client
{
    protected $_connect = null;

    /**
     * Client constructor.
     * @param string $host the server host
     * @param string $username your username
     * @param string|array $auth_info your password or cert
     * @param int $port ssh port default 22
     * @param string $auth_mode
     * @throws \Exception
     */
    public function __construct($host, $username, $auth_info, $port = 22, $auth_mode = 'pwd')
    {
        if (!function_exists('ssh2_connect'))
        {
            $this->_excetpion_handle("Not Install php-ssh2 Extension!");
        }

        //获取ssh的链接
        $this->_connect = ssh2_connect($host, $port);
        if (!$this->_connect)
        {
            $this->_excetpion_handle('Connect Faild:' . "Connect Info[host:{$host} port:{$port}]");
        }

        //登录ssh
        $this->getAuth($username, $auth_info, $auth_mode);
    }

    /**
     * get login auth
     * @param string $username your username
     * @param array|string $auth_info
     * @param string $auth_mode
     * @return $this
     * @throws \Exception
     */
    private function getAuth($username, $auth_info, $auth_mode)
    {
        $authRet = false;
        if ($auth_mode == 'pwd')//密码登录
        {
            $authRet = ssh2_auth_password($this->_connect, $username, $auth_info);
        }
        elseif ($auth_mode == 'crt')//证书登录
        {
            if (!is_array($auth_info))
            {
                $this->_excetpion_handle('Auth_Mode: crt,But Auth_info Is Not Array');
            }
            $needAuthInfo = array('pub_cert', 'priv_cert', 'cert_pwd');
            foreach ($needAuthInfo as $value) //检查证书登录必选项
            {
                if (!isset($auth_info[$value]))
                {
                    $this->_excetpion_handle('Auth_Mode: crt,But Auth_info Is Not Has ' . $value);
                }
            }
            if (empty($auth_info['cert_pwd']) == true)//无证书密码
            {
                $authRet = ssh2_auth_pubkey_file($this->_connect, $username, $auth_info['pub_cert'], $auth_info['priv_cert']);
            }
            else
            {
                $authRet = ssh2_auth_pubkey_file($this->_connect, $username, $auth_info['pub_cert'], $auth_info['priv_cert'], $auth_info['cert_pwd']);
            }
        }
        else
        {
            $this->_excetpion_handle('Auth_Mode Is Not Alow,your auth mode is ' . $auth_mode);
        }

        if (!$authRet)
        {
            $this->_excetpion_handle('Auth Faild:[username:' . $username . 'auth_info:' . json_encode($auth_info) . ']');
        }
        return $this;
    }

    /**
     * exec linux command
     * @param string $cmd a commade
     * @param bool $is_return
     * @return bool|mixed
     */
    public function exec($cmd, $is_return = true)
    {
        $stream = ssh2_exec($this->_connect, $cmd);
        if ($is_return === true)
        {
            return $this->getRunCmdResult($stream);
        }
        return true;
    }

    /**
     * @param $stream
     * @return mixed
     */
    private function getRunCmdResult($stream)
    {
        $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
        stream_set_blocking($errorStream, true);
        stream_set_blocking($stream, true);
        $ret['res']   = stream_get_contents($stream);
        $ret['error'] = stream_get_contents($errorStream);
        fclose($stream);
        fclose($errorStream);
        return $ret;

    }

    /**
     * __destruct
     */
    public function __destruct()
    {
        $this->exec('exit', false);
        $this->_connect = null;
    }

    /**
     * @param $exception_msg
     * @param int $code
     * @throws \Exception
     */
    private function _excetpion_handle($exception_msg, $code = 0)
    {
        throw new \Exception($exception_msg, $code);
    }
}