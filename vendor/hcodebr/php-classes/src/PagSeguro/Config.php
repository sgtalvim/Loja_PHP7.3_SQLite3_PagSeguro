<?php

namespace Hcode\PagSeguro;

class Config {

    // para mudar de SANDBOX para PRODUÇÃO, alterar o valor de true para false
    const SANDBOX = true;
    
	const SANDBOX_EMAIL = "claudiopaiva1@yahoo.com.br";
	const PRODUCTION_EMAIL = "###";

	const SANDBOX_TOKEN = "D57359FC95F54581888153F1BDD4D547";
	const PRODUCTION_TOKEN = "###";

	//const SANDBOX_SESSIONS = "https://sandbox.api.pagseguro.com";
	//const PRODUCTION_SESSIONS = "https://api.pagseguro.com";

    const SANDBOX_SESSIONS = "https://ws.sandbox.pagseguro.uol.com.br/v2/sessions";
    const PRODUCTION_SESSIONS = "https://ws.pagseguro.uol.com.br/v2/sessions";

    const SANDBOX_URL_JS = "https://stc.sandbox.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js";
    const PRODUCTION_URL_JS = "https://stc.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js";

    const SANDBOX_URL_TRANSACTION = "https://ws.sandbox.pagseguro.uol.com.br/v2/transactions";
    const PRODUCTION_URL_TRANSACTION = "https://ws.pagseguro.uol.com.br/v2/transactions";
    
    const SANDBOX_URL_NOTIFICATION = "https://ws.sandbox.pagseguro.uol.com.br/v3/transactions/notifications/";
	const PRODUCTION_URL_NOTIFICATION =	"https://ws.pagseguro.uol.com.br/v3/transactions/notifications/";

    const MAX_INSTALLMENT_NO_INTEREST = 3;
    const MAX_INSTALLMENT = 10;

    const NOTIFICATION_URL = "https://www.html5dev.com.br/payment/notification";

    public static function getAuthentication():array
	{

		if (Config::SANDBOX === true)
		{

			return array(
				"email"=>Config::SANDBOX_EMAIL,
				"token"=>Config::SANDBOX_TOKEN
			);

		} else {

			return array(
				"email"=>Config::PRODUCTION_EMAIL,
				"token"=>Config::PRODUCTION_TOKEN
			);

		}

	}

	public static function getUrlSessions():string
	{

		return (Config::SANDBOX === true) ? Config::SANDBOX_SESSIONS : Config::PRODUCTION_SESSIONS;

	}

	public static function getUrlJS()
	{

		return (Config::SANDBOX === true) ? Config::SANDBOX_URL_JS : Config::PRODUCTION_URL_JS;

	}

	public static function getUrlTransaction()
	{

		return (Config::SANDBOX === true) ? Config::SANDBOX_URL_TRANSACTION :
		Config::PRODUCTION_URL_TRANSACTION;

	}

	public static function getNotificationTransactionURL()
	{

		return (Config::SANDBOX === true) ? Config::SANDBOX_URL_NOTIFICATION :
		Config::PRODUCTION_URL_NOTIFICATION;

	}

}