<?php

namespace Presenter;

use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Presenter;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Utils\Json;

/**
 * @todo Fill desc.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class ApiPresenter extends Presenter
{

	/** @var IRequest @inject */
	public $httpRequest;

	/** @var IResponse @inject */
	public $httpResponse;

	/**
	 * @param array|string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getPost($name, $default = NULL)
	{
		$contentType = $this->httpRequest->getHeader('Content-Type');
		list ($mime) = explode(';', $contentType);

		$data = $mime === 'application/json'
			? Json::decode($this->httpRequest->getRawBody(), Json::FORCE_ARRAY)
			: $this->httpRequest->getPost();

		if (!is_array($name)) {
			$name = [$name];
		}

		foreach ($name as $key) {
			if (!isset($data[$key])) {
				if (func_num_args() === 1) {
					$name = implode('.', $name);
					$this->sendError(IResponse::S400_BAD_REQUEST, 'missingRequiredValue', "Missing required value under key '$name' in json that was received in request body.");
				}

				return $default;
			}

			$data = $data[$key];
		}

		return $data;
	}

	/**
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getQuery($name, $default = NULL)
	{
		$value = $this->httpRequest->getQuery($name);

		if ($value === NULL) {
			if (func_num_args() === 1) {
				$this->sendError(IResponse::S400_BAD_REQUEST, 'missingRequiredValue', "Missing required value for query parameter named '$name'.");
			}

			return $default;
		}

		return $value;
	}

	/**
	 * @param array $data
	 * @param int $code
	 */
	public function sendJson($data, $code = IResponse::S200_OK)
	{
		$this->httpResponse->setCode($code);

		parent::sendJson($data);
	}

	/**
	 */
	public function sendEmpty()
	{
		$this->httpResponse->setCode(IResponse::S204_NO_CONTENT);

		$this->sendResponse(new TextResponse(''));
	}

	/**
	 * @param int $code
	 * @param string $type
	 * @param string $message
	 */
	public function sendError($code, $type, $message = NULL)
	{
		$error = ['type' => $type];

		if ($message) {
			$error['message'] = $message;
		}

		$this->sendJson($error, $code);
	}

}
