<?php 

use Illuminate\Database\Capsule\Manager as Database;

if (!function_exists('database')) {

    function database()
    {
        $db = new Database;

        $db->addConnection([
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'cip',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);
        $db->setAsGlobal();
        return $db;
    }
}
