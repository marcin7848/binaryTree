<?php
session_start();

require_once "./db_connect.php";
require_once './controller.php';


$kontroler = new controller();
$kontroler->processRequest();