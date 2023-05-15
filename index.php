<?php
header('Content-Type: application/json');

require_once("FlexSQL.php");

$flexsql = new FlexSQL("localhost", "flexsql", "root", "");
// $data = [
//     [],
//     ['John Doe2', 'test', 'test'],
//     ['John Doe3', 'test', 'test'],
// ];

// $columns = ['name', 'surname', 'email'];

// $flexsql->minsert("test", $data, $columns);

// $flexsql->mdelete("test", "1 = 1", ['id', [1,2, 3]]);