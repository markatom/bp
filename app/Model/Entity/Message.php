<?php

namespace Model\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="MessageRepository")
 * @ORM\InheritanceType("JOINED")
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
abstract class Message extends BaseEntity
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
	 * @ORM\ManyToOne(targetEntity="Order")
	 * @var Order
	 */
	protected $order;

	/**
	 * @ORM\ManyToMany(targetEntity="Document", inversedBy="messages")
	 * @var ArrayCollection
	 */
	protected $documents;

	/**
	 * @param Order $order
	 * @param string $subject
	 * @param string $content
	 * @param array $documents
	 */
	public function __construct(Order $order, $subject, $content, array $documents)
	{
		$this->subject   = $subject;
		$this->content   = $content;
		$this->createdAt = new DateTime;
		$this->order     = $order;
		$this->documents = new ArrayCollection($documents);

		foreach ($documents as $document) {
			$document->addMessage($this);
		}
	}

}
