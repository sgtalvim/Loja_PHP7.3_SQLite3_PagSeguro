<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model {

	const SESSION = "User";
	const SECRET = "HcodePhp7_Secret";
	const SECRET_IV = "HcodePhp7_Secret_IV";
	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
	const SUCCESS = "UserSuccess";

	public static function getFromSession()
	{


		$user = new User();

		if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0) {

			$user->setData($_SESSION[User::SESSION]);

		}

		return $user;

	}

	public static function checkLogin($inadmin = true)
	{

		if (
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
		) {
			//Não está logado
			return false;

		} else {

			if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) {

				return true;

			} else if ($inadmin === false) {

				return true;

			} else {

				return false;

			}

		}

	}

	public static function login($login, $password)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE a.deslogin = :LOGIN", array(
			":LOGIN"=>$login
		)); 

		if (count($results) === 0)
		{
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}

		$data = $results[0];

		//$password = User::getPasswordHash($password);

		if (password_verify($password, $data["despassword"]) === true)
		{

			$user = new User();

			$data['desperson'] = utf8_encode($data['desperson']);

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		} else {
			throw new \Exception("Usuário inexistente ou senha inválida.");
			//throw new \Exception("Usuário inexistente ou senha inválida."."<br>".$password."<br>".$data["despassword"]);
		}

	}

	public static function verifyLogin($inadmin = true)
	{

		if (!User::checkLogin($inadmin)) {

			if ($inadmin) {
				header("Location: /pqdt/login");
			} else {
				header("Location: /login");
			}
			exit;

		}

	}

	public static function logout()
	{

		$_SESSION[User::SESSION] = NULL;

	}

	public static function listAll()
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");

	}

	// método save() original do curso, utilizando procedures
	/*
	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]);

	}
	*/

	// método save() adaptado para uso com o SQLite
	public function save()
	{

		$sql = new Sql();

		// pegando a data e hora atual
		$datetime = new \DateTime(date("Y-m-d H:i:s"));
		// formatando para deixar no padrão e inserindo no atributo dtregister
		$this->setdtregister($datetime->format("Y-m-d H:i:s"));

		$sql->query("INSERT INTO tb_persons (desperson, desemail, nrphone, dtregister) VALUES (:desperson, :desemail, :nrphone, :dtregister)", array(
			":desperson"=>utf8_decode($this->getdesperson()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":dtregister"=>$this->getdtregister()
		));

		$idperson = $sql->select("SELECT idperson FROM tb_persons WHERE desperson = :desperson AND desemail = :desemail AND nrphone = :nrphone", array(
			":desperson"=>utf8_decode($this->getdesperson()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone()
		));

		$this->setidperson($idperson[0]["idperson"]);

		$sql->query("INSERT INTO tb_users (idperson, deslogin, despassword, inadmin, dtregister) VALUES (:idperson, :deslogin, :despassword, :inadmin, :dtregister)", array(
			":idperson"=>$this->getidperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":inadmin"=>$this->getinadmin(),
			":dtregister"=>$this->getdtregister()
		));

		$iduser = $sql->select("SELECT iduser FROM tb_users WHERE idperson = :idperson", array(
			":idperson"=>$this->getidperson()
		));

		$this->setiduser($iduser[0]["iduser"]);

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			":iduser"=>$this->getiduser()
		));

		$this->setData($results[0]);
 
	}

	public function get($iduser)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			":iduser"=>$iduser
		));

		$data = $results[0];

		$data['desperson'] = utf8_encode($data['desperson']);


		$this->setData($data);

	}

	// método save() original do curso, utilizando procedures
	/*
	public function update()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"=>$this->getiduser(),
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]);		

	}
	*/

	// método update para o SQLite, sem procedures
	public function update()
	{
		$sql = new Sql();

		$sql->query("UPDATE tb_persons SET desperson = :desperson, desemail = :desemail, nrphone = :nrphone WHERE idperson = :idperson", array(
			":desperson"=>utf8_decode($this->getdesperson()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":idperson"=>$this->getidperson()
		));

		$sql->query("UPDATE tb_users SET deslogin = :deslogin, despassword = :despassword, inadmin = :inadmin WHERE iduser = :iduser", array(
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":inadmin"=>$this->getinadmin(),
			":iduser"=>$this->getiduser()
		));

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			":iduser"=>$this->getiduser()
		));

		$this->setData($results[0]);
	}

	// criei esse método para atualizar os dados da sessão após o update dos dados do usuário logado
	// faltava isso na aula 122 pra fechar no dez, atualizando o formulário após a edição
	public function updateSession($values)
	{
		$_SESSION[User::SESSION] = $values;
	}

	/*
	// método delete() com procedure
	public function delete()
	{

		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));

	}
	*/

	// método delete() adaptado para o SQLite
	public function delete()
	{

		$sql = new Sql();

		/*
		    DELETE FROM tb_addresses WHERE idperson = :idperson;
		    DELETE FROM tb_addresses WHERE idaddress IN(SELECT idaddress FROM tb_orders WHERE iduser = :iduser);
		*/
		$sql->query("
			DELETE FROM tb_persons WHERE idperson = :idperson;
			", array(
			":idperson"=>$this->getidperson()
		));

		/*
		    DELETE FROM tb_userslogs WHERE iduser = :iduser;
		    DELETE FROM tb_userspasswordsrecoveries WHERE iduser = :iduser;
		    DELETE FROM tb_orders WHERE iduser = :iduser;
		    DELETE FROM tb_cartsproducts WHERE idcart IN(SELECT idcart FROM tb_carts WHERE iduser = :iduser);
		    DELETE FROM tb_carts WHERE iduser = :iduser;
		*/
		$sql->query("
			DELETE FROM tb_users WHERE iduser = :iduser;
			", array(
			":iduser"=>$this->getiduser()
		));

	}

	public static function getForgot($email, $inadmin = true)
	{

		$sql = new Sql();

		$results = $sql->select("
			SELECT *
			FROM tb_persons a
			INNER JOIN tb_users b USING(idperson)
			WHERE a.desemail = :email;
		", array(
			":email"=>$email
		));

		if (count($results) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha.");
			
		}
		else
		{

			$data = $results[0];

			/*
			// uso da procedure com o MYSQL
			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"=>$data["iduser"],
				":desip"=>$_SERVER["REMOTE_ADDR"]
			));
			*/

			// pegando a data e hora atual
			$datetime = new \DateTime(date("Y-m-d H:i:s"));
			// formatando para deixar no padrão e inserindo no atributo dtregister
			$dtregister = $datetime->format("Y-m-d H:i:s");

			// adaptação para o SQLite
			$sql->query("INSERT INTO tb_userspasswordsrecoveries (iduser, desip, dtregister) VALUES(:iduser, :desip, :dtregister)", array(
				":iduser"=>$data["iduser"],
				":desip"=>$_SERVER["REMOTE_ADDR"],
				":dtregister"=>$dtregister
			));

			$results2 = $sql->select("SELECT * FROM tb_userspasswordsrecoveries WHERE dtregister = :dtregister AND iduser = :iduser", array(
				":dtregister"=>$dtregister,
				":iduser"=>$data["iduser"]
			));

			if (count($results2) === 0)
			{

				throw new \Exception("Não foi possível recuperar a senha");

			}
			else
			{

				$dataRecovery = $results2[0];

				//a função mcrypt_encrypt() usada nesta aula foi depreciada no PHP 7.1
				//$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));

				// substituindo a função mcrypt_encrypt()
				$code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

				$code = base64_encode($code);

				if ($inadmin === true) {
					
					//$link = "http://www.hcodecommerce.com.br/pqdt/forgot/reset?code=$code";
					$link = "http://lojahcode.com/pqdt/forgot/reset?code=$code";

				} else {

					//$link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code";
					$link = "http://lojahcode.com/forgot/reset?code=$code";

				}


				$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir Senha da Hcode Store", "forgot", array(
					"name"=>$data["desperson"],
					"link"=>$link
				));

				$mailer->send();

				return $data;

			}


		}

	}

	public static function validForgotDecrypt($code)
	{
		// adaptando o cálculo de uma hora atrás para o limite do uso da senha
		// data hora atual, convertida em timestamp com strtotime()
		$now = strtotime(date("Y-m-d H:i:s"));

		// uma hora em segundos
		$diftime = 3600;

		// subtrai uma hora de agora para saber o limite da sessão de troca de senha
		$timelimit = $now - $diftime;

		$umahoraatras = date("Y-m-d H:i:s", $timelimit);

		// a função mcrypt_decrypt() usada nesta aula foi depreciada no PHP 7.1
		//$idrecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET, base64_decode($code), MCRYPT_MODE_ECB);

		// substituindo a função mcrypt_decrypt()
		$code = base64_decode($code);

		$idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

		$sql = new Sql();

		$results = $sql->select("
			SELECT * 
			FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING(iduser)
			INNER JOIN tb_persons c USING(idperson)
			WHERE 
				a.idrecovery = :idrecovery
			    AND
			    a.dtrecovery IS NULL
			    AND
			    a.dtregister >= :umahoraatras;
		", array(
			":idrecovery"=>$idrecovery,
			":umahoraatras"=>$umahoraatras
		));

		if (count($results) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha.");
		}
		else
		{

			return $results[0];

		}

	}

	public static function setForgotUsed($idrecovery)
	{
		$now = date("Y-m-d H:i:s");

		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = :now WHERE idrecovery = :idrecovery", array(
			":now"=>$now,
			":idrecovery"=>$idrecovery
		));

	}

	public function setPassword($password)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>User::getPasswordHash($password),
			":iduser"=>$this->getiduser()
		));

	}

	public static function setError($msg)
	{

		$_SESSION[User::ERROR] = $msg;

	}

	public static function getError()
	{

		$msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';

		User::clearError();

		return $msg;

	}

	public static function clearError()
	{

		$_SESSION[User::ERROR] = NULL;

	}

	public static function setSuccess($msg)
	{

		$_SESSION[User::SUCCESS] = $msg;

	}

	public static function getSuccess()
	{

		$msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';

		User::clearSuccess();

		return $msg;

	}

	public static function clearSuccess()
	{

		$_SESSION[User::SUCCESS] = NULL;

	}

	public static function setErrorRegister($msg)
	{

		$_SESSION[User::ERROR_REGISTER] = $msg;

	}

	public static function getErrorRegister()
	{

		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';

		User::clearErrorRegister();

		return $msg;

	}

	public static function clearErrorRegister()
	{

		$_SESSION[User::ERROR_REGISTER] = NULL;

	}

	public static function checkLoginExist($login)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [
			':deslogin'=>$login
		]);

		return (count($results) > 0);

	}

	public static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_DEFAULT, [
			'cost'=>12
		]);

	}

	public function getOrders()
	{

		$sql = new Sql();

		$results = $sql->select("
			SELECT * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus) 
			INNER JOIN tb_carts c USING(idcart)
			INNER JOIN tb_users d ON d.iduser = a.iduser
			INNER JOIN tb_addresses e USING(idaddress)
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			WHERE a.iduser = :iduser
		", [
			':iduser'=>$this->getiduser()
		]);

		return $results;

	}

	// apenas no MySQL: SELECT SQL_CALC_FOUND_ROWS *
	/*
	public static function getPage($page = 1, $itemsPerPage = 10)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_users a
			INNER JOIN tb_persons b USING(idperson) 
			ORDER BY b.desperson
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
			FROM tb_users a 
			INNER JOIN tb_persons b USING(idperson)
			WHERE b.desperson LIKE :search OR b.desemail = :search OR a.deslogin LIKE :search
			ORDER BY b.desperson
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

		// apenas no MySQL: SELECT SQL_CALC_FOUND_ROWS *
		// no SQLite adaptei assim
		$results = $sql->select("
			SELECT *
			FROM tb_users a
			INNER JOIN tb_persons b USING(idperson) 
			ORDER BY b.desperson
			LIMIT $start, $itemsPerPage;
		");

		// no MySQL:
		//$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		// adaptando ao SQLite, com uma viagem no BD, conto apenas os resultados da busca
		// sem buscar por LIMIT
		$results2 = $sql->select("
			SELECT COUNT(*)
			FROM tb_users a
			INNER JOIN tb_persons b USING(idperson) 
			ORDER BY b.desperson;
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
			FROM tb_users a 
			INNER JOIN tb_persons b USING(idperson)
			WHERE b.desperson LIKE :search OR b.desemail = :search OR a.deslogin LIKE :search
			ORDER BY b.desperson
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
			FROM tb_users a 
			INNER JOIN tb_persons b USING(idperson)
			WHERE b.desperson LIKE :search OR b.desemail = :search OR a.deslogin LIKE :search
			ORDER BY b.desperson;
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