<?php

namespace Model\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class Document extends BaseEntity
{

    use Identifier;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $type;

	/**
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 */
	protected $createdAt;

	/**
	 * @ORM\Column(type="blob")
	 * @var string
	 */
	protected $data;

	/**
	 * @ORM\ManyToOne(targetEntity="Order")
	 * @var Order
	 */
	protected $order;

	/**
	 * @ORM\ManyToMany(targetEntity="Message", mappedBy="documents")
	 * @var Collection
	 */
	protected $messages;

	/**
	 * @param string $name
	 * @param string $type
	 * @param string $data
	 * @param Order $order
	 */
	public function __construct($name, $type, $data, Order $order)
	{
		$this->name      = $name;
		$this->type      = $type;
		$this->createdAt = new DateTime;
		$this->data      = $data;
		$this->order     = $order;
		$this->messages  = new ArrayCollection;
	}

}
