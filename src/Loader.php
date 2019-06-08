<?php

namespace SymphonyPDO;

use PDO;
use Symphony;

final class Loader
{
    private static $connection = null;
    private static $credentials = null;

    // Prevents the class from being instanciated
    private function __construct()
    {
    }

    public static function isConnected()
    {
        return self::getConnection() instanceof Lib\Database;
    }

    public static function closeConnection()
    {
        self::$connection = null;
        self::$credentials = null;
    }

    public static function instance(\StdClass $credentials = null)
    {
        if (null !== $credentials && $credentials !== self::$credentials) {
            self::closeConnection();
            self::setCredentials($credentials);
        }

        if (!self::isConnected()) {
            self::init();
        }

        return self::getConnection();
    }

    public static function bind(Lib\Database $connection, \StdClass $credentials = null)
    {
        self::$connection = $connection;
        if (null !== $credentials) {
            self::setCredentials($credentials);
        }
    }

    public static function getConnection()
    {
        return self::$connection;
    }

    public static function getCredentials()
    {
        if (null === self::$credentials) {
            if (!(Symphony::Configuration() instanceof \Configuration)) {
                throw new Lib\Exceptions\DatabaseException("No credentials were supplied to SymphonyPDO::Loader and Symphony doesn't appear to have been initialised. There is no Configuration class to obtain credentials from.");
            }
            self::setCredentials((object) Symphony::Configuration()->get('database'));
        }

        return self::$credentials;
    }

    private static function setCredentials(\StdClass $credentials)
    {
        self::$credentials = $credentials;
    }

    private static function init()
    {
        self::bind(new Lib\Database(
            sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=utf8',
                'mysql',
                self::getCredentials()->host,
                self::getCredentials()->port,
                self::getCredentials()->db
            ),
            self::getCredentials()->user,
            self::getCredentials()->password,
            [
                'table-prefix' => (string) self::getCredentials()->tbl_prefix,
            ],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]
        ));
    }
}
