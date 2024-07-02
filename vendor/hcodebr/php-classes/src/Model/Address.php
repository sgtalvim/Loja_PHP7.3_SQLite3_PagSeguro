<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Address extends Model {

	const SESSION_ERROR = "AddressError";

	public static function getCEP($nrcep)
	{

		$nrcep = str_replace("-", "", $nrcep);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "http://viacep.com.br/ws/$nrcep/json/");

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$data = json_decode(curl_exec($ch), true);

		curl_close($ch);

		return $data;

	}

	public function loadFromCEP($nrcep)
	{

		$data = Address::getCEP($nrcep);

		if (isset($data['logradouro']) && $data['logradouro']) {

			$this->setdesaddress($data['logradouro']);
			$this->setdescomplement($data['complemento']);
			$this->setdesdistrict($data['bairro']);
			$this->setdescity($data['localidade']);
			$this->setdesstate($data['uf']);
			$this->setdescountry('Brasil');
			$this->setdeszipcode($nrcep);

		}

	}

	// método save() original do curso, utilizando procedures
	/*
	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_addresses_save(:idaddress, :idperson, :desaddress, :desnumber, :descomplement, :descity, :desstate, :descountry, :deszipcode, :desdistrict)", [
			':idaddress'=>$this->getidaddress(),
			':idperson'=>$this->getidperson(),
			':desaddress'=>utf8_decode($this->getdesaddress()),
			':desnumber'=>$this->getdesnumber(),
			':descomplement'=>utf8_decode($this->getdescomplement()),
			':descity'=>utf8_decode($this->getdescity()),
			':desstate'=>utf8_decode($this->getdesstate()),
			':descountry'=>utf8_decode($this->getdescountry()),
			':deszipcode'=>$this->getdeszipcode(),
			':desdistrict'=>$this->getdesdistrict()
		]);

		if (count($results) > 0) {
			$this->setData($results[0]);
		}

	}
	*/

	// método save() adaptado para uso com o SQLite
	public function save()
	{

		// pegando a data e hora atual
		$datetime = new \DateTime(date("Y-m-d H:i:s"));
		// formatando para deixar no padrão e inserindo no atributo dtregister
		$this->setdtregister($datetime->format("Y-m-d H:i:s"));

		$sql = new Sql();

		$pidaddress = $this->getidaddress();

		// se o número não for informado, cadastrar como zero
		if (!$this->getdesnumber()) $this->setdesnumber(0);

		if(!isset($pidaddress)){
// utf8_decode ... não estava salvando o original...
			$results = $sql->query("INSERT INTO tb_addresses (idperson, desaddress, desnumber, descomplement, descity, desstate, descountry, deszipcode, desdistrict, dtregister) VALUES (:idperson, :desaddress, :desnumber, :descomplement, :descity, :desstate, :descountry, :deszipcode, :desdistrict, :dtregister)", [
				':idperson'=>$this->getidperson(),
				':desaddress'=>$this->getdesaddress(),
				':desnumber'=>$this->getdesnumber(),
				':descomplement'=>$this->getdescomplement(),
				':descity'=>$this->getdescity(),
				':desstate'=>$this->getdesstate(),
				':descountry'=>$this->getdescountry(),
				':deszipcode'=>$this->getdeszipcode(),
				':desdistrict'=>$this->getdesdistrict(),
				":dtregister"=>$this->getdtregister()
			]);

			$pidaddress = $sql->select("SELECT idaddress FROM tb_addresses WHERE idperson = :idperson  AND dtregister = :dtregister", array(
				":idperson"=>$this->getidperson(),
				":dtregister"=>$this->getdtregister()
			));

			$this->setidaddress($pidaddress[0]["idaddress"]);

		} else {

			$sql->query("UPDATE tb_addresses SET idperson = :idperson, desaddress = :desaddress, desnumber = :desnumber, descomplement = :descomplement, descity = :descity, desstate = :desstate, descountry = :descountry, deszipcode = :deszipcode, desdistrict = :desdistrict WHERE idaddress = :idaddress", array(
				':idperson'=>$this->getidperson(),
				':desaddress'=>$this->getdesaddress(),
				':desnumber'=>$this->getdesnumber(),
				':descomplement'=>$this->getdescomplement(),
				':descity'=>$this->getdescity(),
				':desstate'=>$this->getdesstate(),
				':descountry'=>$this->getdescountry(),
				':deszipcode'=>$this->getdeszipcode(),
				':desdistrict'=>$this->getdesdistrict(),
				':idaddress'=>$this->getidaddress()
			));

		}

		$results = $sql->select("SELECT * FROM tb_addresses WHERE idaddress = :idaddress", array(
			':idaddress'=>$this->getidaddress()
		));

		if (count($results) > 0) {
			$this->setData($results[0]);
		}

	}

	public static function setMsgError($msg)
	{

		$_SESSION[Address::SESSION_ERROR] = $msg;

	}

	public static function getMsgError()
	{

		$msg = (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR] : "";

		Address::clearMsgError();

		return $msg;

	}

	public static function clearMsgError()
	{

		$_SESSION[Address::SESSION_ERROR] = NULL;

	}

}

 ?>