<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

// página adm produtos
$app->get("/pqdt/products", function(){

	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
	
	// forma antiga, listava todos na mesma tela:
	// $products = Product::listAll();

	// com paginação, se quiser mudar qtd por página, passar o 2º parâmetro
	// nesse caso dois por página
	// $pagination = User::getPage($page, 2);

	if ($search != '') {

		$pagination = Product::getPageSearch($search, $page);

	} else {

		$pagination = Product::getPage($page);

	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++) { 

		array_push($pages, [
			'href'=>'/pqdt/products?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);

	}

	$page = new PageAdmin();

	$page->setTpl("products", [
		"products"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	]);
});

// página criar produtos
$app->get("/pqdt/products/create", function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("products-create");
});

// processa criar produto
$app->post("/pqdt/products/create", function(){

	User::verifyLogin();

	$product = new Product();

	$product->setData($_POST);

	$product->save();

	header("Location: /pqdt/products");
	exit;
});

// página editar produtos
$app->get("/pqdt/products/:idproduct", function($idproduct){

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$page = new PageAdmin();

	$page->setTpl("products-update", [
		'product'=>$product->getValues()
	]);
});

// página update editar produtos
// certificar que a biblioteca GD está instalada
$app->post("/pqdt/products/:idproduct", function($idproduct){

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$product->setData($_POST);

	$product->save();

	$product->setPhoto($_FILES["file"]);

	header("Location: /pqdt/products");
	exit;
});

// processa excluir produto
$app->get("/pqdt/products/:idproduct/delete", function($idproduct){

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$product->delete();

	header("Location: /pqdt/products");
	exit;
});

?>