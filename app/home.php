<?php 


// $db = database();

// $rows = $db->table('branch')->limit(100)->get();
// echo '<pre>';
// foreach($rows as $row){
//     print_r($row);
// }

$data = ['title' => 'home'];
view('welcome', $data);
