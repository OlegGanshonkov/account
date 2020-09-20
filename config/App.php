<?php

/**
 * Class App
 */

class App
{
    /**
     * @var string
     */
    private static $root;

    /**
     * @var Database
     */
    private static $db;

    /**
     * Initialization app
     */
    public static function init()
    {
        // manual connection of classes
        require_once(self::root() . '/config/Database.php');
        require_once(self::root() . '/models/Model.php');
        require_once(self::root() . '/models/AccountModel.php');
        require_once(self::root() . '/models/CompositionModel.php');
        require_once(self::root() . '/Account.php');

        // init db connection


        // set default time zone
        date_default_timezone_set('Europe/Moscow');
        ini_set('date.timezone','Europe/Moscow');
    }

    /**
     * @return Database
     */
    public static function getDb(): Database
    {
        if (!self::$db){
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    /**
     * Project root path
     * @return string
     */
    public static function root(): string
    {
        if (!self::$root) {
            self::$root = realpath(dirname(__FILE__) . '/../');
        }
        return self::$root;
    }

}

