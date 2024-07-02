<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

// rota para o admin
$app->get('/pqdt', function() {

	User::verifyLogin();
    
	$page = new PageAdmin();

	$page->setTpl("index");

});

// rota para o admin/login
$app->get('/pqdt/login', function() {
    
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("login");

});

// rota para autenticar no admin/login
$app->post('/pqdt/login', function() {
    
	User::login($_POST["login"], $_POST["password"]);

	header("Location: /pqdt");
	exit;

});

// rota para destruir o login
$app->get('/pqdt/logout', function() {
    
	User::logout();

	header("Location: /pqdt/login");
	exit;

});

// rota para o admin/forgot
$app->get('/pqdt/forgot', function() {
    
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");

});

//
$app->post("/pqdt/forgot", function(){
	
	$user = User::getForgot($_POST["email"]);

	header("Location: /pqdt/forgot/sent");
	exit;

});

// rota para o admin/forgot/sent
$app->get('/pqdt/forgot/sent', function() {
    
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-sent");

});

// rota para redefinir senha
$app->get('/pqdt/forgot/reset', function() {

	$user = User::validForgotDecrypt($_GET['code']);
    
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET['code']
	));

});

// rota para segunda verificação de senha
$app->post('/pqdt/forgot/reset', function() {

	$forgot = User::validForgotDecrypt($_POST['code']);

	User::setForgotUsed($forgot['idrecovery']);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$user->setPassword($_POST['password']);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset-success");

});

?>