<?php

namespace App;

use Nette\Application\Routers\RouteList;
use Router\ApiRoute;

/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return \Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList;

		$router[] = new ApiRoute('sessions', 'Sessions:create', ApiRoute::METHOD_POST);
		$router[] = new ApiRoute('sessions', 'Sessions:delete', ApiRoute::METHOD_DELETE);

		$router[] = new ApiRoute('user/me', 'User:readMe', ApiRoute::METHOD_GET);

		return $router;
	}

}
