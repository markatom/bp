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
		$router[] = new ApiRoute('sessions/current', 'Sessions:deleteCurrent', ApiRoute::METHOD_DELETE);
		$router[] = new ApiRoute('sessions/current', 'Sessions:readCurrent', ApiRoute::METHOD_GET);

		$router[] = new ApiRoute('roles', 'Roles:readAll', ApiRoute::METHOD_GET);

		$router[] = new ApiRoute('users', 'Users:readAll', ApiRoute::METHOD_GET);
		$router[] = new ApiRoute('users/<id>', 'Users:read', ApiRoute::METHOD_GET);
		$router[] = new ApiRoute('users/<id>', 'Users:update', ApiRoute::METHOD_PUT);

		return $router;
	}

}
