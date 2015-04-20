<?php

namespace Model\Entity;

use DateTime;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="`order`")
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class Order extends BaseEntity
{

    use Identifier;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $name;

	/**
	 * @ORM\Embedded(class="OrderState")
	 * @var OrderState
	 */
	protected $state;

	/**
	 * @ORM\Embedded(class="Accident")
	 * @var Event
	 */
	protected $event;

	/**
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 */
	protected $createdAt;

	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 * @var User
	 */
	protected $createdBy;

	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 * @var User
	 */
	protected $assignee;

	/**
	 * @ORM\ManyToOne(targetEntity="Client")
	 * @var Client
	 */
	protected $client;

	/**
	 * @param string $name
	 * @param Event $event
	 * @param User $createdBy
	 * @param Client $client
	 * @param User $assignee
	 */
	public function __construct($name, Event $event, User $createdBy, Client $client, User $assignee = NULL)
	{
		$this->name      = $name;
		$this->event     = $event;
		$this->createdAt = new DateTime;
		$this->createdBy = $createdBy;
		$this->client    = $client;
		$this->assignee  = $assignee;
		$this->state     = new OrderState;
	}

}
