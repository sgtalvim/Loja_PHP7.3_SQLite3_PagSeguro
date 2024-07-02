<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app->get('/pqdt/users/:iduser/password', function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-password", [
		"user"=>$user->getValues(),
		"msgError"=>User::getError(),
		"msgSuccess"=>User::getSuccess()
	]);
});

$app->post('/pqdt/users/:iduser/password', function($iduser){

	User::verifyLogin();

	if (!isset($_POST['despassword']) || $_POST['despassword'] === '') {
		User::setError("Preencha a nova senha!");
		header("Location: /pqdt/users/$iduser/password");
		exit;
	}

	if (!isset($_POST['despassword-confirm']) || $_POST['despassword-confirm'] === '') {
		User::setError("Preencha a confirmação da nova senha!");
		header("Location: /pqdt/users/$iduser/password");
		exit;
	}

	if ($_POST['despassword'] !== $_POST['despassword-confirm']) {
		User::setError("A confirmação diverge da senha informada!");
		header("Location: /pqdt/users/$iduser/password");
		exit;
	}

	$user = new User();

	$user->get((int)$iduser);

	$user->setPassword($_POST['despassword']);

	User::setSuccess("Senha alterada com sucesso!");
	header("Location: /pqdt/users/$iduser/password");
	exit;

});

// rota para admin listar usuários
$app->get('/pqdt/users', function() {

	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	// forma antiga, listava todos na mesma tela:
	// $users = User::listAll();

	// com paginação, se quiser mudar qtd por página, passar o 2º parâmetro
	// nesse caso dois por página
	// $pagination = User::getPage($page, 2);

	if ($search != '') {

		$pagination = User::getPageSearch($search, $page);

	} else {

		$pagination = User::getPage($page);

	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++) { 

		array_push($pages, [
			'href'=>'/pqdt/users?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);

	}
    
	$page = new PageAdmin();

	$page->setTpl("users", array(
		"users"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	));

});

// rota para admin criar usuários
$app->get('/pqdt/users/create', function() {

	User::verifyLogin();
    
	$page = new PageAdmin();

	$page->setTpl("users-create");

});

// rota para admin excluir usuário
$app->get('/pqdt/users/:iduser/delete', function($iduser) {

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /pqdt/users");
	exit;

});

// rota para admin alterar usuário
$app->get('/pqdt/users/:iduser', function($iduser) {

	User::verifyLogin();
    
	$page = new PageAdmin();

	$user = new User();

	$user->get((int)$iduser);

	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));

});

// rota para admin salvar usuário
$app->post('/pqdt/users/create', function() {

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$user->setData($_POST);

	$user->save();

	header("Location: /pqdt/users");
	exit;

});

// rota para admin salvar usuário editado
$app->post('/pqdt/users/:iduser', function($iduser) {

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$user->setData($_POST);

	$user->update();

	header("Location: /pqdt/users");
	exit;

});

?>