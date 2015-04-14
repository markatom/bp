<?php

namespace Model\Entity;

use LogicException;
use Nette\Object;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Strings;

/**
 * @ORM\Embeddable
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class Address extends Object
{

	const ZIP_PATTERN = '~^(\d{3}) ?(\d{2})$~';

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
    protected $street;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $city;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $zip;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $country;

	/**
	 * @param string $zip
	 */
	public function setZip($zip)
	{
		if (!$m = Strings::match($zip, self::ZIP_PATTERN)) {
			throw new LogicException('Invalid zip code.');
		}

		$this->zip = $m[1] . ' ' . $m[2]; // normalize
	}

}
