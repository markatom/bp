<?php

namespace Model\Entity;

use Kdyby\Doctrine\Entities\BaseEntity;
use LogicException;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Strings;

/**
 * Immutable representation of address.
 *
 * @ORM\Embeddable
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class Address extends BaseEntity
{

	const ZIP_PATTERN = '~^(\d{3}) ?(\d{2})$~';

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
    protected $street;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	protected $city;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	protected $zip;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	protected $country;

	/**
	 * @param string $street
	 * @param string $city
	 * @param string $zip
	 * @param string $country
	 */
	public function __construct($street, $city, $zip, $country)
	{
		$this->street  = $street;
		$this->city    = $city;
		$this->zip     = $this->normalizeZip($zip);
		$this->country = $country;
	}

	/**
	 * @param string $street
	 * @return Address
	 */
	public function setStreet($street)
	{
		return new self($street, $this->city, $this->zip, $this->country);
	}

	/**
	 * @param string $city
	 * @return Address
	 */
	public function setCity($city)
	{
		return new self($this->street, $city, $this->zip, $this->country);
	}

	/**
	 * @param string $zip
	 * @return Address
	 */
	public function setZip($zip)
	{
		return new self($this->street, $this->city, $this->normalizeZip($zip), $this->country);
	}

	/**
	 * @param string $country
	 * @return Address
	 */
	public function setCountry($country)
	{
		return new self($this->street, $this->city, $this->zip, $country);
	}

	/**
	 * @param string $zip
	 * @return string
	 */
	private function normalizeZip($zip)
	{
		if ($zip === NULL) {
			return NULL;
		}

		if (!$m = Strings::match($zip, self::ZIP_PATTERN)) {
			throw new LogicException('Invalid zip code.');
		}

		return $m[1] . ' ' . $m[2];
	}

}
