<?php
/**
 * @author: 秋田嘉
 * @email: 997348985@qq.com
 * @fileName PDOSingleton.php
 * @date: 2018/7/6 10:24
 * @describe: PDO单例类
 */

namespace QiuTianJia\MySQLDDIC\Tools;

class PDOSingleton extends \PDO
{
    protected static $_instance;

    public function __construct(string $dsn, string $username, string $passwd, array $options = [])
    {
        return parent::__construct($dsn, $username, $passwd, $options);
    }

    public static function getInstance(string $dsn, string $username, string $passwd, array $options = [])
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self($dsn, $username, $passwd, $options);
        }
        return self::$_instance;
    }
}