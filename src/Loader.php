<?php

namespace SymphonyPDO;

use PDO, Symphony;
use SymphonyPDO\Lib;

final class Loader implements \Singleton
{
    private static $connection;

    public static function instance()
    {
        if (!(self::$connection instanceof Lib\Database)) {
            self::$connection = self::init();
        }

        return self::$connection;
    }

    private static function init()
    {
        $details = (object) Symphony::Configuration()->get('database');
        self::$connection = new Lib\Database(
            sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=utf8',
                    'mysql',
                    $details->host,
                    $details->port,
                    $details->db
            ),
            $details->user,
            $details->password,
            [
                'table-prefix' => (string) $details->tbl_prefix,
            ],
            [
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION,
            ]
        );

        return self::$connection;
    }
}
