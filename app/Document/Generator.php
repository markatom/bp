<?php

namespace Document;

use Model\Entity\Order;
use RuntimeException;

/**
 * Interface for document generators.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
interface Generator
{

	/**
	 * Generates document for given order.
	 * @param Order $order
	 * @return string
	 * @throws MissingMandatoryItemsException
	 */
    function generate(Order $order);

}

class MissingMandatoryItemsException extends RuntimeException
{

	/** @var array */
	protected $items;

	/**
	 * @param array $items
	 * @param string $message
	 * @param int $code
	 * @param \Exception $previous
	 */
	public function __construct(array $items, $message = '', $code = 0, $previous = NULL)
	{
		$this->items = $items;

	    parent::__construct($message, $code, $previous);
	}

	/**
	 * @return array
	 */
	public function getItems()
	{
		return $this->items;
	}

}
