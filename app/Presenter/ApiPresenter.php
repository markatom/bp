<?php

namespace Presenter;

use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Presenter;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Utils\Json;

/**
 * Ancestor for all resources' controllers.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
abstract class ApiPresenter extends Presenter
{

	/** Format of date used in api responses. */
	const DATE_FORMAT = 'Y-m-d';

	/** Format of date and time used in api respones. */
	const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

	/** @var IRequest @inject */
	public $httpRequest;

	/** @var IResponse @inject */
	public $httpResponse;

	/**
	 * Returns data sent in body of post request.
	 * Can to iterate nested structures if given name is an array.
	 * Sends error with description if default value is not provided and requested value is missing.
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
	 * Returns data sent as query parameters.
	 * Can to iterate nested structures if given name is an array.
	 * Sends error with description if default value is not provided and requested value is missing.
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getQuery($name, $default = NULL)
	{
		$data = $this->httpRequest->getQuery();

		if (!is_array($name)) {
			$name = [$name];
		}

		foreach ($name as $key) {
			if (!isset($data[$key])) {
				if (func_num_args() === 1) {
					$nested = array_slice($name, 1);

					$name = $name[0] . (
						$nested
							? '[' . implode('][', $nested) . ']'
							: ''
					);

					$this->sendError(IResponse::S400_BAD_REQUEST, 'missingRequiredValue', "Missing required value for query parameter named '$name'.");
				}

				return $default;
			}

			$data = $data[$key];
		}

		return $data;
	}

	/**
	 * Sends a data in JSON format with given HTTP status code.
	 * @param array $data
	 * @param int $code
	 */
	public function sendJson($data, $code = IResponse::S200_OK)
	{
		$this->httpResponse->setCode($code);

		parent::sendJson($data);
	}

	/**
	 * Sends a response with empty body with HTTP No Content status code.
	 */
	public function sendEmpty()
	{
		$this->httpResponse->setCode(IResponse::S204_NO_CONTENT);

		$this->sendResponse(new TextResponse(''));
	}

	/**
	 * Sends an error as JSON object with a type of the error and an optional message describing further details.
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
