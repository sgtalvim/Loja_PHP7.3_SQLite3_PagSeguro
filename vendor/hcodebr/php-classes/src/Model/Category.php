<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Category extends Model {

	public static function listAll()
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");

	}

	// método save() original do curso, utilizando procedures
	/*
	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
		));

		$this->setData($results[0]);

		Category::updateFile();

	}
	*/

	// método save() adaptado para uso com o SQLite
	// esse método salva um novo e também faz o update se for caso
	// serve para criar uma nova ou atualizar uma categoria existente
	public function save()
	{

		$sql = new Sql();

		$idcategory = $this->getidcategory();

		if(!isset($idcategory)){

			// pegando a data e hora atual
			$datetime = new \DateTime(date("Y-m-d H:i:s"));
			// formatando para deixar no padrão e inserindo no atributo dtregister
			$this->setdtregister($datetime->format("Y-m-d H:i:s"));

			$sql->query("INSERT INTO tb_categories (descategory, dtregister) VALUES (:descategory, :dtregister)", array(
				":descategory"=>$this->getdescategory(),
				":dtregister"=>$this->getdtregister()
			));

			$idcategory = $sql->select("SELECT idcategory FROM tb_categories WHERE descategory = :descategory AND dtregister = :dtregister", array(
				":descategory"=>$this->getdescategory(),
				":dtregister"=>$this->getdtregister()
			));

			$this->setidcategory($idcategory[0]["idcategory"]);

		} else {
			$sql->query("UPDATE tb_categories SET descategory = :descategory WHERE idcategory = :idcategory", array(
				":descategory"=>$this->getdescategory(),
				":idcategory"=>$this->getidcategory()
			));
		}

		$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(
			":idcategory"=>$this->getidcategory()
		));

		$this->setData($results[0]);

		Category::updateFile();
 
	}

	public function get($idcategory)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", [
			':idcategory'=>$idcategory
		]);

		$this->setData($results[0]);

	}

	public function delete()
	{

		$sql = new Sql();

		$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [
			':idcategory'=>$this->getidcategory()
		]);

		Category::updateFile();

	}

	public static function updateFile()
	{

		$categories = Category::listAll();

		$html = [];

		foreach ($categories as $row) {
			array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
		}

		file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));

	}

	public function getProducts($related = true)
	{

		$sql = new Sql();

		if ($related === true) {

			return $sql->select("
				SELECT * FROM tb_products WHERE idproduct IN(
					SELECT a.idproduct
					FROM tb_products a
					INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
					WHERE b.idcategory = :idcategory
				);
			", [
				':idcategory'=>$this->getidcategory()
			]);

		} else {

			return $sql->select("
				SELECT * FROM tb_products WHERE idproduct NOT IN(
					SELECT a.idproduct
					FROM tb_products a
					INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
					WHERE b.idcategory = :idcategory
				);
			", [
				':idcategory'=>$this->getidcategory()
			]);

		}

	}

	public function getProductsPage($page = 1, $itemsPerPage = 8)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		// apenas no MySQL: SELECT SQL_CALC_FOUND_ROWS *
		// no SQLite adaptei assim
		$results = $sql->select("
			SELECT *
			FROM tb_products a
			INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
			INNER JOIN tb_categories c ON c.idcategory = b.idcategory
			WHERE c.idcategory = :idcategory
			LIMIT $start, $itemsPerPage;
		", [
			':idcategory'=>$this->getidcategory()
		]);

		// no MySQL:
		//$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		// adaptando ao SQLite, com uma viagem no BD, conto apenas os resultados da busca
		$results2 = $sql->select("
			SELECT COUNT(*)
			FROM tb_products a
			INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
			INNER JOIN tb_categories c ON c.idcategory = b.idcategory
			WHERE c.idcategory = :idcategory;
		", [
			':idcategory'=>$this->getidcategory()
		]);

		$resultTotal[0]["nrtotal"] = (int)$results2[0]["COUNT(*)"];

		return [
			'data'=>Product::checkList($results),
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];

	}

	public function addProduct(Product $product)
	{

		$sql = new Sql();

		$sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES(:idcategory, :idproduct)", [
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
		]);

	}

	public function removeProduct(Product $product)
	{

		$sql = new Sql();

		$sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct", [
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
		]);

	}
			
	// apenas no MySQL: SELECT SQL_CALC_FOUND_ROWS *
	/*
	public static function getPage($page = 1, $itemsPerPage = 10)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_categories 
			ORDER BY descategory
			LIMIT $start, $itemsPerPage;
		");

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];

	}

	public static function getPageSearch($search, $page = 1, $itemsPerPage = 10)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_categories 
			WHERE descategory LIKE :search
			ORDER BY descategory
			LIMIT $start, $itemsPerPage;
		", [
			':search'=>'%'.$search.'%'
		]);

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];

	}
	*/

	public static function getPage($page = 1, $itemsPerPage = 10)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT *
			FROM tb_categories 
			ORDER BY descategory
			LIMIT $start, $itemsPerPage;
		");

		// no MySQL:
		//$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		// adaptando ao SQLite, com uma viagem no BD, conto apenas os resultados da busca
		// sem buscar por LIMIT
		$results2 = $sql->select("
			SELECT COUNT(*)
			FROM tb_categories 
			ORDER BY descategory;
		");

		$resultTotal[0]["nrtotal"] = (int)$results2[0]["COUNT(*)"];

		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];

	}

	public static function getPageSearch($search, $page = 1, $itemsPerPage = 10)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT *
			FROM tb_categories 
			WHERE descategory LIKE :search
			ORDER BY descategory
			LIMIT $start, $itemsPerPage;
		", [
			':search'=>'%'.$search.'%'
		]);

		// no MySQL:
		//$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		// adaptando ao SQLite, com uma viagem no BD, conto apenas os resultados da busca
		// sem buscar por LIMIT
		$results2 = $sql->select("
			SELECT COUNT(*)
			FROM tb_categories 
			WHERE descategory LIKE :search
			ORDER BY descategory;
		", [
			':search'=>'%'.$search.'%'
		]);

		$resultTotal[0]["nrtotal"] = (int)$results2[0]["COUNT(*)"];

		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];

	}

}

 ?>