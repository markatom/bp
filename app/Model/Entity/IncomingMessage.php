<?php

namespace Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class IncomingMessage extends Message
{

	/**
	 * @ORM\ManyToOne(targetEntity="Addressable")
	 * @var Addressable
	 */
	protected $sender;

	/**
	 * @param Order $order
	 * @param string $subject
	 * @param string $content
	 * @param Document[] $documents
	 * @param Addressable $sender
	 */
	public function __construct(Order $order, $subject, $content, array $documents, Addressable $sender)
	{
		parent::__construct($order, $subject, $content, $documents);

		$this->sender = $sender;
	}

}
