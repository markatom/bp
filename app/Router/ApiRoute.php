<?php

namespace Router;

use Nette\Application\Request;
use Nette\Application\Routers\Route;
use Nette\Http\IRequest;

/**
 * @todo Fill desc.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class ApiRoute extends Route
{

	const API_URL_PREFIX = 'api/';

	const METHOD_POST   = 1024;
	const METHOD_GET    = 2048;
	const METHOD_PUT    = 4096;
	const METHOD_DELETE = 8192;

	private $flags;

	/**
	 * @param string $mask URL mask, e.g. '<presenter>/<action>/<id \d{1,3}>'.
	 * @param array|string $metadata Default values or metadata.
	 * @param int $flags
	 */
    public function __construct($mask, $metadata = [], $flags = 0)
    {
		$this->flags = $flags;

        parent::__construct(self::API_URL_PREFIX . $mask, $metadata, $flags);
    }

	/**
	 * @param IRequest $httpRequest
	 * @return Request|NULL
	 */
	public function match(IRequest $httpRequest)
	{
		$appRequest = parent::match($httpRequest);

		if ($this->flags === 0) {
			return $appRequest;
		}

		if ($appRequest) {
			$method = $httpRequest->getMethod();

			$match = $method === 'POST' && $this->flags & self::METHOD_POST
				|| $method === 'GET' && $this->flags & self::METHOD_GET
				|| $method === 'PUT' && $this->flags & self::METHOD_PUT
				|| $method === 'DELETE' && $this->flags & self::METHOD_DELETE;

			if ($match) {
				return $appRequest;
			}
		}

		return NULL;
	}

}
