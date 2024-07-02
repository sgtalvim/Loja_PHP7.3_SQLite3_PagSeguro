<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;

// rota para admin categorias
$app->get('/pqdt/categories', function() {

	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
	
	// forma antiga, listava todos na mesma tela:
	// $categories = Category::listAll();

	// com paginação, se quiser mudar qtd por página, passar o 2º parâmetro
	// nesse caso dois por página
	// $pagination = User::getPage($page, 2);

	if ($search != '') {

		$pagination = Category::getPageSearch($search, $page);

	} else {

		$pagination = Category::getPage($page);

	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++) { 

		array_push($pages, [
			'href'=>'/pqdt/categories?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);

	}

	$page = new PageAdmin();

	$page->setTpl("categories", [
		"categories"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	]);

});

// rota para admin criar categorias (form)
$app->get('/pqdt/categories/create', function() {

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("categories-create");

});

// rota para admin criar categorias (recebe form)
$app->post('/pqdt/categories/create', function() {

	User::verifyLogin();

	$category = new Category();

	$category->setData($_POST);

	$category->save();

	header("Location: /pqdt/categories");
	exit;

});

// rota para admin deletar categorias
$app->get('/pqdt/categories/:idcategory/delete', function($idcategory) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header("Location: /pqdt/categories");
	exit;

});

// rota para admin editar categorias (form)
$app->get('/pqdt/categories/:idcategory', function($idcategory) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-update", [
		'category'=>$category->getValues()
	]);

});

// rota para admin editar categorias
$app->post('/pqdt/categories/:idcategory', function($idcategory) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->setData($_POST);

	$category->save();

	header("Location: /pqdt/categories");
	exit;

});

// rota para produtos por categorias
$app->get('/pqdt/categories/:idcategory/products', function($idcategory) {

	User::verifyLogin();
	
	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-products", [
		'category'=>$category->getValues(),
		'productsRelated'=>$category->getProducts(),
		'productsNotRelated'=>$category->getProducts(false)
	]);

});

// rota para add produtos na categoria
$app->get('/pqdt/categories/:idcategory/products/:idproduct/add', function($idcategory, $idproduct) {

	User::verifyLogin();
	
	$category = new Category();

	$category->get((int)$idcategory);

	$product = new Product();

	$product->get((int)$idproduct);

	$category->addProduct($product);

	header("Location: /pqdt/categories/".$idcategory."/products");
	exit;
});

// rota para remover produtos da categoria
$app->get('/pqdt/categories/:idcategory/products/:idproduct/remove', function($idcategory, $idproduct) {

	User::verifyLogin();
	
	$category = new Category();

	$category->get((int)$idcategory);

	$product = new Product();

	$product->get((int)$idproduct);

	$category->removeProduct($product);

	header("Location: /pqdt/categories/".$idcategory."/products");
	exit;
});

?>