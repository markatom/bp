<?php

namespace Model\Entity;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Random;

/**
 * @ORM\Entity
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class Session extends BaseEntity
{

    use Identifier;

	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 * @var User
	 */
	protected $user;

	/**
	 * @ORM\Column(type="string", unique=true)
	 * @var string
	 */
	protected $token;

	/**
	 * @ORM\Column(type="datetime")
	 * @var \DateTime
	 */
	protected $created;

	/**
	 * @ORM\Column(type="boolean")
	 * @var bool
	 */
	protected $longLife;

	/**
	 * @ORM\Column(type="datetime")
	 * @var \DateTime
	 */
	protected $expiration;

	/** Expiration for short life session. */
	const SHORT_LIFE = '+10 min';
	
	/** Expiration for long life session. */
	const LONG_LIFE = '+7 days';

	/**
	 * @param User $user
	 * @param bool $longLife
	 */
	public function __construct(User $user, $longLife)
	{
		$this->user     = $user;
		$this->token    = Random::generate('40', '0-9a-zA-Z');
		$this->created  = new \DateTime;
		$this->longLife = $longLife;

		$this->renew();
	}

	/**
	 * Renews session expiration.
	 */
	public function renew()
	{
		$lifetime = $this->longLife
			? self::LONG_LIFE
			: self::SHORT_LIFE;

		$this->expiration = new \DateTime($lifetime);
	}

}
