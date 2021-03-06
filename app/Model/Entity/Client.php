<?php

namespace Model\Entity;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use LogicException;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;

/**
 * @ORM\Entity
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class Client extends Addressable
{

	const TELEPHONE_PATTERN = '~^
		(                # begin of optional country code
			(00|\+)      # 00 or +
			(?<p>\d{3})  # three digits stored in p
		)?               # end of country code
		[ ]?             # optional space
		(?<a>\d{3})      # three digits stored in a
		[ ]?             # optional space
		(?<b>\d{3})      # three digits stored in b
		[ ]?             # optional space
		(?<c>\d{3})      # three digits stored in c
	$~x';

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $fullName;

	/**
	 * @ORM\Column(type="date", nullable=true)
	 * @var \DateTime
	 */
	protected $dateOfBirth;

	/**
	 * @ORM\Column(type="string", unique=true, nullable=true)
	 * @var string
	 */
	protected $email;

	/**
	 * @ORM\Column(type="string", unique=true, nullable=true)
	 * @var string
	 */
	protected $telephone;

	/**
	 * @ORM\Embedded(class="Address")
	 * @var Address
	 */
	protected $address;

	/**
	 * @param string $fullName
	 * @param string|DateTime $dateOfBirth
	 * @param string $email
	 * @param string $telephone
	 * @param Address $address
	 */
	public function __construct($fullName, $dateOfBirth, $email, $telephone, Address $address)
	{
		$this->fullName = $fullName;
		$this->address  = $address;

		$this->setEmail($email);
		$this->setDateOfBirth($dateOfBirth);
		$this->setTelephone($telephone);
	}

	/**
	 * @param string $telephone
	 */
	public function setTelephone($telephone)
	{
		if ($telephone === NULL) {
			$this->telephone = NULL;
			return;
		}

		if (!$m = Strings::match($telephone, self::TELEPHONE_PATTERN)) {
			throw new LogicException('Invalid telephone number.');
		}

		$p = $m['p'] ?: '420'; // default country code for the Czech Republic

		$this->telephone = '+' . $p . ' ' . $m['a'] . ' ' . $m['b'] . ' ' . $m['c']; // normalize
	}

	/**
	 * @param string $dateOfBirth
	 */
	public function setDateOfBirth($dateOfBirth)
	{
		if ($dateOfBirth === NULL) {
			$this->dateOfBirth = NULL;
			return;
		}

		try {
			$dateOfBirth = DateTime::from($dateOfBirth);

		} catch (\Exception $e) {
			throw new LogicException('Invalid date of birth.');
		}

		$this->dateOfBirth = $dateOfBirth;
	}

	/**
	 * Sets the email address and validates it only if is not empty.
	 * @param string $email
	 * @return string
	 */
	public function setEmail($email)
	{
		if ($email === NULL) {
			$this->email = NULL;
			return;
		}

		parent::setEmail($email);
	}

}
