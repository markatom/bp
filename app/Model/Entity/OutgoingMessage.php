<?php

namespace Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class OutgoingMessage extends IncomingMessage
{

	/**
	 * @ORM\ManyToOne(targetEntity="Addressable")
	 * @var Addressable
	 */
	protected $recipient;

	/**
	 * @param Order $order
	 * @param string $subject
	 * @param string $content
	 * @param Document[] $documents
	 * @param Addressable $sender
	 * @param Addressable $recipient
	 */
	public function __construct(Order $order, $subject, $content, array $documents, Addressable $sender, Addressable $recipient)
	{
		parent::__construct($order, $subject, $content, $documents, $sender);

		$this->recipient = $recipient;
	}

}
