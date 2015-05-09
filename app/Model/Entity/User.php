<?php

namespace Model\Entity;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use LogicException;

/**
 * @ORM\Entity
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class User extends Addressable
{

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $fullName;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	private $password;

	/**
	 * @ORM\ManyToOne(targetEntity="Role")
	 * @var Role
	 */
	protected $role;

	/**
	 * @ORM\Column(type="boolean")
	 * @var bool
	 */
	private $deleted = FALSE;

	/**
	 * @param string $fullName
	 * @param string $email
	 * @param Role $role
	 */
	public function __construct($fullName, $email, Role $role)
	{
		$this->fullName = $fullName;
		$this->email    = $email;
		$this->role     = $role;
	}

	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @param string $password
	 * @return bool
	 */
	public function authenticate($password)
	{
		return password_verify($password, $this->password);
	}

	/**
	 * @param string $password
	 */
	public function setPassword($password)
	{
		$this->password = $this->hash($password);
	}

	/**
	 */
	public function delete()
	{
		$this->deleted = TRUE;
	}

	/**
	 * @param string $password
	 * @return string
	 */
	private function hash($password)
	{
		return password_hash($password, PASSWORD_DEFAULT);
	}

}

class InvalidOriginalPasswordException extends \RuntimeException { }
