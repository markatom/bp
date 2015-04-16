<?php

namespace Model\Entity;

use Kdyby\Doctrine\Entities\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use LogicException;
use Nette\Utils\Validators;

/**
 * Entity with an email address.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
abstract class Addressable extends BaseEntity
{

	/**
	 * @ORM\Column(type="string", unique=true)
	 * @var string
	 */
	protected $email;

	/**
	 * @param string $email
	 */
	public function setEmail($email)
	{
		if (!Validators::isEmail($email)) {
			throw new LogicException('Invalid email.');
		}

		$this->email = $email;
	}

}
