<?php

namespace SIL\Webonary;

use MongoDB\Client;
use MongoDB\Database;
use MongoDB\Driver\ServerApi;

class Mongo
{

	/**
	 * @return Database
	 * @noinspection DuplicatedCode
	 */
	public static function GetMongoDB(): Database
	{
		$settings = WEBONARY_MONGO;
		$catalog = $settings['cat'];

		$uri = "mongodb+srv://{$settings['usr']}:{$settings['pwd']}@{$settings['url']}/?retryWrites=true&w=majority&appName=Cluster0";

		// set the version of the Stable API on the client
		$api_version = new ServerApi(ServerApi::V1);

		// create a new client and connect to the server
		$client = new Client($uri, [], ['serverApi' => $api_version]);

		return $client->$catalog;
	}
}
