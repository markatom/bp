<?php

namespace Model\Entity;

use DateTime;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Random;

/**
 * @ORM\Entity
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class Token extends BaseEntity
{

    use Identifier;

	/**
	 * @ORM\Column(type="string", unique=true, name="`key`")
	 * @var string
	 */
	protected $key;

	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 * @var User
	 */
	protected $user;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $type;

	/**
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 */
	protected $expiration;

	/**
	 * @param User $user
	 * @param string $type
	 * @param DateTime $expiration
	 */
	public function __construct(User $user, $type, DateTime $expiration)
	{
		$this->key        = Random::generate(20, '0-9a-zA-Z');
		$this->user       = $user;
		$this->type       = $type;
		$this->expiration = $expiration;
	}

}
