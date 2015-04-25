<?php

namespace Model\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class Message extends BaseEntity
{

    use Identifier;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $subject;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $content;

	/**
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 */
	protected $createdAt;

	/**
	 * @ORM\ManyToMany(targetEntity="Document", inversedBy="messages")
	 * @var ArrayCollection
	 */
	protected $documents;

	/**
	 * @ORM\ManyToOne(targetEntity="Addressable")
	 * @var Addressable
	 */
	protected $sender;

	/**
	 * @ORM\ManyToOne(targetEntity="Addressable")
	 * @var Addressable
	 */
	protected $recipient;

	/**
	 * @ORM\ManyToOne(targetEntity="Order")
	 * @var Order
	 */
	protected $order;

	/**
	 * @param Order $order
	 * @param Addressable $sender
	 * @param Addressable $recipient
	 * @param string $subject
	 * @param string $content
	 * @param Document[] $documents
	 */
	public function __construct(Order$order, Addressable $sender, Addressable $recipient, $subject, $content, $documents = [])
	{
		$this->subject   = $subject;
		$this->content   = $content;
		$this->createdAt = new DateTime;
		$this->sender    = $sender;
		$this->recipient = $recipient;
		$this->documents = new ArrayCollection($documents);
		$this->order     = $order;
	}

}
