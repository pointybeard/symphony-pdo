<?php

namespace SymphonyPDO;

use PDO;
use Symphony;
use SymphonyPDO\Lib;

final class Loader implements \Singleton
{
    private static $connection;

    public static function instance(\StdClass $credentials=null)
    {
        if (!(self::$connection instanceof Lib\Database)) {
            // Try to load in the credentials from Symphony if nothing was
            // supplied. This is just for backwards compatiblity. Eventually
            // credentials will always be required to be passed in.
            if(is_null($credentials)) {
                if(!(Symphony::Configuration() instanceof \Configuration)) {
                    throw new Lib\Exceptions\DatabaseException("No credentials supplied to SymphonyPDO Loader and Symphony doesn't appear to have been initialised so there is no Configuration class.");
                }
                $credentials = (object) Symphony::Configuration()->get('database');
            }

            self::$connection = self::init($credentials);
        }

        return self::$connection;
    }

    private static function init(\StdClass $credentials=null)
    {
        self::$connection = new Lib\Database(
            sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=utf8',
                    'mysql',
                    $credentials->host,
                    $credentials->port,
                    $credentials->db
            ),
            $credentials->user,
            $credentials->password,
            [
                'table-prefix' => (string) $credentials->tbl_prefix,
            ],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]
        );

        return self::$connection;
    }
}
