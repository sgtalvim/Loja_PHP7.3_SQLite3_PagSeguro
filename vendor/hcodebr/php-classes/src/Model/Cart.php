<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;

class Cart extends Model {

	const SESSION = "Cart";
	const SESSION_ERROR = "CartError";

	public static function getFromSession()
	{

		$cart = new Cart();

		if (isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0) {

			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);

		} else {

			$cart->getFromSessionID();

			if (!(int)$cart->getidcart() > 0) {

				$data = [
					'dessessionid'=>session_id()
				];

				if (User::checkLogin(false)) {

					$user = User::getFromSession();
					
					$data['iduser'] = $user->getiduser();	

				}

				$cart->setData($data);

				$cart->save();

				$cart->setToSession();


			}

		}

		return $cart;

	}

	public function setToSession()
	{

		$_SESSION[Cart::SESSION] = $this->getValues();

	}

	public function getFromSessionID()
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
			':dessessionid'=>session_id()
		]);

		if (count($results) > 0) {

			$this->setData($results[0]);

		}

	}	

	public function get(int $idcart)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
			':idcart'=>$idcart
		]);

		if (count($results) > 0) {

			$this->setData($results[0]);

		}

	}

	// método save() original do curso, utilizando procedures
	/*
	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
			':idcart'=>$this->getidcart(),
			':dessessionid'=>$this->getdessessionid(),
			':iduser'=>$this->getiduser(),
			':deszipcode'=>$this->getdeszipcode(),
			':vlfreight'=>$this->getvlfreight(),
			':nrdays'=>$this->getnrdays()
		]);

		$this->setData($results[0]);

	}
	*/

	// método save() adaptado para uso com o SQLite
	// esse método salva um novo e também faz o update se for caso
	// serve para criar uma nova compra ou atualizar uma existente
	public function save()
	{

		$sql = new Sql();

		$idcart = $this->getidcart();

		if(!isset($idcart)){

			// pegando a data e hora atual
			$datetime = new \DateTime(date("Y-m-d H:i:s"));
			// formatando para deixar no padrão e inserindo no atributo dtregister
			$this->setdtregister($datetime->format("Y-m-d H:i:s"));

			$results = $sql->query("INSERT INTO tb_carts (dessessionid, iduser, deszipcode, vlfreight, nrdays, dtregister) VALUES (:dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays, :dtregister)", [
				':dessessionid'=>$this->getdessessionid(),
				':iduser'=>$this->getiduser(),
				':deszipcode'=>$this->getdeszipcode(),
				':vlfreight'=>$this->getvlfreight(),
				':nrdays'=>$this->getnrdays(),
				":dtregister"=>$this->getdtregister()
			]);

			$idcart = $sql->select("SELECT idcart FROM tb_carts WHERE dessessionid = :dessessionid AND dtregister = :dtregister", array(
				":dessessionid"=>$this->getdessessionid(),
				":dtregister"=>$this->getdtregister()
			));

			$this->setidcart($idcart[0]["idcart"]);

		} else {
			$sql->query("UPDATE tb_carts SET dessessionid = :dessessionid, iduser = :iduser, deszipcode = :deszipcode, vlfreight = :vlfreight, nrdays = :nrdays WHERE idcart = :idcart", array(
				':idcart'=>$this->getidcart(),
				':dessessionid'=>$this->getdessessionid(),
				':iduser'=>$this->getiduser(),
				':deszipcode'=>$this->getdeszipcode(),
				':vlfreight'=>$this->getvlfreight(),
				':nrdays'=>$this->getnrdays()
			));
		}

		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", array(
			":idcart"=>$this->getidcart()
		));

		$this->setData($results[0]);
	}

	public function addProduct(Product $product)
	{

		// pegando a data e hora atual
		$datetime = new \DateTime(date("Y-m-d H:i:s"));
		// formatando para deixar no padrão e inserindo no atributo dtregister
		$this->setdtregister($datetime->format("Y-m-d H:i:s"));

		$sql = new Sql();

		$sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct, dtregister) VALUES(:idcart, :idproduct, :dtregister)", [
			':idcart'=>$this->getidcart(),
			':idproduct'=>$product->getidproduct(),
			':dtregister'=>$this->getdtregister()
		]);

		$this->getCalculateTotal();

	}

	public function removeProduct(Product $product, $all = false)
	{

		// pegando a data e hora atual
		$datetime = new \DateTime(date("Y-m-d H:i:s"));
		// formatando para deixar no padrão e inserindo no atributo dtregister
		$this->setdtremoved($datetime->format("Y-m-d H:i:s"));

		$sql = new Sql();

		if ($all) {
			// ver a query original do curso, com NOW() do MySQL
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = :dtremoved WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", [
				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct(),
				':dtremoved'=>$this->getdtremoved()
			]);

		} else {
			// ver a query original do curso, com NOW() do MySQL
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = :dtremoved WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [
				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct(),
				':dtremoved'=>$this->getdtremoved()
			]);

		}

		$this->getCalculateTotal();

	}

	public function getProducts()
	{

		$sql = new Sql();

		$rows = $sql->select("
			SELECT b.idproduct, b.desproduct , b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal 
			FROM tb_cartsproducts a 
			INNER JOIN tb_products b ON a.idproduct = b.idproduct 
			WHERE a.idcart = :idcart AND a.dtremoved IS NULL 
			GROUP BY b.idproduct, b.desproduct , b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl 
			ORDER BY b.desproduct
		", [
			':idcart'=>$this->getidcart()
		]);

		return Product::checkList($rows);

	}

	public function getProductsTotals()
	{

		$sql = new Sql();

		$results = $sql->select("
			SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight, SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd
			FROM tb_products a
			INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
			WHERE b.idcart = :idcart AND dtremoved IS NULL;
		", [
			':idcart'=>$this->getidcart()
		]);

		if (count($results) > 0) {
			return $results[0];
		} else {
			return [];
		}

	}

	// inventei um cálculo fictício de frete, nada do que é retornado é real...
	public static function frete ($c, $l, $a, $zip) {

		$calc1 = ($c * $l * $a / 6000) + 11.00;

		$valor = str_replace('.', ',', number_format($calc1, 2));

			$prazo = 1 + (int)substr($zip,-3,1) + (int)substr($zip,-2,1) + (int)substr($zip,-1,1);

			return ["prazo"=>$prazo,"valor"=>$valor];

	}

	public function setFreight($nrzipcode)
	{

		$nrzipcode = str_replace('-', '', $nrzipcode);

		$totals = $this->getProductsTotals();

		if ($totals['nrqtd'] > 0) {

			if ($totals['vlheight'] < 2) $totals['vlheight'] = 2;
			if ($totals['vllength'] < 16) $totals['vllength'] = 16;

			$qs = http_build_query([
				'nCdEmpresa'=>'',
				'sDsSenha'=>'',
				'nCdServico'=>'40010',
				'sCepOrigem'=>'09853120',
				'sCepDestino'=>$nrzipcode,
				'nVlPeso'=>$totals['vlweight'],
				'nCdFormato'=>'1',
				'nVlComprimento'=>$totals['vllength'],
				'nVlAltura'=>$totals['vlheight'],
				'nVlLargura'=>$totals['vlwidth'],
				'nVlDiametro'=>'0',
				'sCdMaoPropria'=>'S',
				'nVlValorDeclarado'=>$totals['vlprice'],
				'sCdAvisoRecebimento'=>'S'
			]);
			
			$frete = Cart::frete($totals['vllength'], $totals['vlheight'], $totals['vlwidth'], $nrzipcode);

			//parou de funcionar em outubro de 2023:
			//$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);

			// apenas para testar o código final, fiz a gambiarra transformando os valores que interessam em um objeto
			// para serem acessados pelo método original do professor... e funcionou...
			$xml = (object)["Servicos"=>(object)["cServico"=>(object)["MsgErro"=>"", "PrazoEntrega"=>$frete['prazo'], "Valor"=>$frete['valor']]]];

			$result = $xml->Servicos->cServico;

			if ($result->MsgErro != '') {

				Cart::setMsgError($result->MsgErro);

			} else {

				Cart::clearMsgError();

			}

			$this->setnrdays($result->PrazoEntrega);
			$this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
			$this->setdeszipcode($nrzipcode);

			$this->save();

			return $result;

		} else {



		}

	}

	public static function formatValueToDecimal($value):float
	{

		$value = str_replace('.', '', $value);
		return str_replace(',', '.', $value);

	}

	public static function setMsgError($msg)
	{

		$_SESSION[Cart::SESSION_ERROR] = $msg;

	}

	public static function getMsgError()
	{

		$msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";

		Cart::clearMsgError();

		return $msg;

	}

	public static function clearMsgError()
	{

		$_SESSION[Cart::SESSION_ERROR] = NULL;

	}

	public function updateFreight()
	{

		if ($this->getdeszipcode() != '') {

			$this->setFreight($this->getdeszipcode());

		}

	}

	public function getValues()
	{

		$this->getCalculateTotal();

		return parent::getValues();

	}

	public function getCalculateTotal()
	{

		$this->updateFreight();

		$totals = $this->getProductsTotals();

		$this->setvlsubtotal($totals['vlprice']);
		$this->setvltotal($totals['vlprice'] + (float)$this->getvlfreight());

	}

}

 ?>