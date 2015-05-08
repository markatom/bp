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
	 */
    function generate(Order $order);

}

class MissingMandatoryItemsException extends RuntimeException { }
