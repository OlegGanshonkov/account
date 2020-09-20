<?php

/**
 * Class, Database Connections
 */
final class Database
{
    /**
     * @var mysqli
     */
    private static $_instance;

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }

    /**
     * DB constructor.
     * @throws Exception
     */
    private function __construct()
    {
        self::$_instance = new mysqli('localhost', 'root', '123', 'account');
        if (self::$_instance->connect_error) {
            throw new Exception('MySQL Error (' . self::$_instance->connect_errno . '): ' . self::$_instance->connect_error);
        }
        self::$_instance->query("SET CHARACTER SET utf8");
        self::$_instance->query("SET SESSION collation_connection='utf8_general_ci'");
    }

    /**
     * @return Database|mysqli
     */
    public static function getInstance()
    {
        if (self::$_instance !== null) {
            return self::$_instance;
        }

        return new self;
    }

    /**
     * @param $sql
     * @return mixed
     */
    static function query($sql)
    {
        if (!($result = self::$_instance->query($sql))) {
            throw new Exception('MySQL Error (' . self::$_instance->errno . '): ' . self::$_instance->error . ' [' . $sql . ']');
        }
        return $result;
    }
}