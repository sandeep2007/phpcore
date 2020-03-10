<?php 
use Illuminate\Database\Capsule\Manager as Database;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

// $db = new Database;

// $db->addConnection([
//     'driver'    => 'mysql',
//     'host'      => 'localhost',
//     'database'  => 'cip',
//     'username'  => 'root',
//     'password'  => '',
//     'charset'   => 'utf8',
//     'collation' => 'utf8_unicode_ci',
//     'prefix'    => '',
// ]);
// // Set the event dispatcher used by Eloquent models... (optional)
// $db->setEventDispatcher(new Dispatcher(new Container));

// // Make this Capsule instance available globally via static methods... (optional)
// $db->setAsGlobal();
// // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
// $db->bootEloquent();
// $rows = Database::table('branch')->limit(100)->get();
// echo '<pre>';
// foreach($rows as $row){
//     print_r($row);
// }

$data = ['title' => 'home'];
view('welcome', $data);
