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

	use Identifier;

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
	 * @ORM\Column(type="date")
	 * @var \DateTime
	 */
	protected $dateOfBirth;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $telephone;

	/**
	 * @ORM\Embedded(class="Address")
	 * @var string
	 */
	protected $address;

	/**
	 * @param string $fullName
	 * @param string|DateTime $dateOfBirth
	 * @param string $telephone
	 * @param Address $address
	 */
	public function __construct($fullName, $dateOfBirth, $telephone, Address $address)
	{
		$this->fullName = $fullName;
		$this->address  = $address;

		$this->setDateOfBirth($dateOfBirth);
		$this->setTelephone($telephone);
	}

	/**
	 * @param string $telephone
	 */
	public function setTelephone($telephone)
	{
		if (!$m = Strings::match($telephone, self::TELEPHONE_PATTERN)) {
			throw new LogicException('Invalid telephone number.');
		}

		$p = isset($m['p']) ? $m['p'] : '420'; // default country code for the Czech Republic

		$this->telephone = '+' . $p . ' ' . $m['a'] . ' ' . $m['b'] . ' ' . $m['c']; // normalize
	}

	/**
	 * @param string $dateOfBirth
	 */
	public function setDateOfBirth($dateOfBirth)
	{
		try {
			$dateOfBirth = DateTime::from($dateOfBirth);

		} catch (\Exception $e) {
			throw new LogicException('Invalid date of birth.');
		}

		$this->dateOfBirth = $dateOfBirth;
	}

}
