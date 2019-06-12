<?php

namespace Dooracle;

use Medoo\Medoo;
use think\facade\Config;

class Dooracle
{

    private static $intance=null;

    private static function connection()
    {
        $dbconfig = Config::pull('database');
        $dbconfig['hostport'] = $dbconfig['hostport'] ?: 1521;
        $dbconfig['charset'] = $dbconfig['charset'] ?: 'AL32UTF8';

        $tns = "  
(DESCRIPTION =
    (ADDRESS_LIST =
      (ADDRESS = (PROTOCOL = TCP)(HOST = {$dbconfig['hostname']})(PORT = {$dbconfig['hostport']}))
    )
    (CONNECT_DATA =
      (SERVICE_NAME = {$dbconfig['database']})
    )
)
       ";
        $pdo = new \PDO("oci:dbname=".$tns,$dbconfig['username'],$dbconfig['password']);

        $medoo = new Medoo([
            'pdo' => $pdo,
            'database_type'=>'oracle',
            'charset'=>$dbconfig['charset']
        ]);

        return $medoo;
    }

    /**
     * @return Medoo|null
     */
    public static function getIntance()
    {
        if(self::$intance === null)
        {
            self::$intance = self::connection();
        }
        return self::$intance;
    }





}

