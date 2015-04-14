<?php

namespace Model\Entity;

use Kdyby\Doctrine\Entities\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

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

}
