<?php

namespace Model\Entity;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class User extends BaseEntity
{

    use Identifier;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $fullName;

	/**
	 * @ORM\Column(type="string", unique=true)
	 * @var string
	 */
	protected $email;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	protected $password;

	/**
	 * @param string $fullName
	 * @param string $email
	 * @param string $password
	 */
	public function __construct($fullName, $email, $password)
	{
		$this->fullName = $fullName;
		$this->email    = $email;
		$this->password = $password;
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
	 * @param string $originalPassword
	 * @param string $newPassword
	 */
	public function changePassword($originalPassword, $newPassword)
	{
		if (!$this->authenticate($originalPassword)) {
			throw new InvalidOriginalPasswordException;
		}

		$this->password = $this->hash($newPassword);
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
