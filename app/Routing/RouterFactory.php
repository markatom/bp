<?php

namespace Routing;

use Nette\Application\Routers\RouteList;
use Routing\ApiRoute;

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

		$router[] = new ApiRoute('users', 'Users:create', ApiRoute::METHOD_POST);
		$router[] = new ApiRoute('users', 'Users:readAll', ApiRoute::METHOD_GET);
		$router[] = new ApiRoute('users?token[key]=<tokenKey>', 'Users:updateUserByToken', ApiRoute::METHOD_PUT);
		$router[] = new ApiRoute('users/<id>', 'Users:read', ApiRoute::METHOD_GET);
		$router[] = new ApiRoute('users/<id>', 'Users:update', ApiRoute::METHOD_PUT);
		$router[] = new ApiRoute('users/<id>', 'Users:delete', ApiRoute::METHOD_DELETE);

		$router[] = new ApiRoute('clients', 'Clients:readAll', ApiRoute::METHOD_GET);
		$router[] = new ApiRoute('clients', 'Clients:create', ApiRoute::METHOD_POST);
		$router[] = new ApiRoute('clients/<id>', 'Clients:read', ApiRoute::METHOD_GET);
		$router[] = new ApiRoute('clients/<id>', 'Clients:update', ApiRoute::METHOD_PUT);

		$router[] = new ApiRoute('orders', 'Orders:readAll', ApiRoute::METHOD_GET);
		$router[] = new ApiRoute('orders', 'Orders:create', ApiRoute::METHOD_POST);
		$router[] = new ApiRoute('orders/<id>', 'Orders:read', ApiRoute::METHOD_GET);
		$router[] = new ApiRoute('orders/<id>', 'Orders:update', ApiRoute::METHOD_PUT);

		$router[] = new ApiRoute('order-states', 'OrderStates:readAll', ApiRoute::METHOD_GET);

		$router[] = new ApiRoute('messages', 'Messages:readAll', ApiRoute::METHOD_GET);
		$router[] = new ApiRoute('messages', 'Messages:create', ApiRoute::METHOD_POST);

		$router[] = new ApiRoute('documents', 'Documents:create', ApiRoute::METHOD_POST);
		$router[] = new ApiRoute('documents', 'Documents:readAll', ApiRoute::METHOD_GET);
		$router[] = new ApiRoute('documents/<id>', 'Documents:read', ApiRoute::METHOD_GET);

		return $router;
	}

}
