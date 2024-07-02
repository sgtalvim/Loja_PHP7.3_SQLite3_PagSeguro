<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;

$app->get("/pqdt/orders/:idorder/status", function($idorder){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$page = new PageAdmin();

	$page->setTpl("order-status", [
		'order'=>$order->getValues(),
		'status'=>OrderStatus::listAll(),
		'msgSuccess'=>Order::getSuccess(),
		'msgError'=>Order::getError()
	]);

});

$app->post("/pqdt/orders/:idorder/status", function($idorder){

	User::verifyLogin();

	if (!isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0) {
		Order::setError("Informe o status atual!");
		header("Location: /pqdt/orders/".$idorder."/status");
		exit;
	}

	$order = new Order();

	$order->get((int)$idorder);

	$order->setidstatus((int)$_POST['idstatus']);

	$order->save();

	Order::setSuccess("Status atualizado!");

	header("Location: /pqdt/orders/".$idorder."/status");
	exit;
});

$app->get("/pqdt/orders/:idorder/delete", function($idorder){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$order->delete();

	header("Location: /pqdt/orders");
	exit;

});

$app->get("/pqdt/orders/:idorders", function($idorder){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$cart = $order->getCart();

	$page = new PageAdmin();

	$page->setTpl("order", [
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts()
	]);
});

$app->get("/pqdt/orders", function(){

	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	// com paginação, se quiser mudar qtd por página, passar o 2º parâmetro
	// nesse caso dois por página
	// $pagination = User::getPage($page, 2);

	if ($search != '') {

		$pagination = Order::getPageSearch($search, $page);

	} else {

		$pagination = Order::getPage($page);

	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++) { 

		array_push($pages, [
			'href'=>'/pqdt/orders?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);

	}

	$page = new PageAdmin();

	$page->setTpl("orders", [
		//'orders'=>Order::listAll()
		"orders"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	]);
});

?>