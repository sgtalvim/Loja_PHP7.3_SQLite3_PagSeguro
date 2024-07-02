<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;

$app = new Slim();

$app->config('debug', true);

require_once("functions.php");
require_once("site.php");
require_once("site-pagseguro.php");
require_once("adm.php");
require_once("adm-users.php");
require_once("adm-categories.php");
require_once("adm-products.php");
require_once("adm-orders.php");

$app->run();

 ?>