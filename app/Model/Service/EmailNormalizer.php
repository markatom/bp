<?php

namespace Model\Service;

use Nette\Object;
use Nette\Utils\Strings;

/**
 * Service for email text normalizing.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class EmailNormalizer extends Object
{

	const REPLY = '~(\n.*:\n*)?(\n>.*)+(\n|$)~';

	/**
	 * Normalize email text.
	 * @param string $text
	 * @return string
	 */
	public function normalize($text)
	{
		$text = $this->convertLineBreaks($text);
		$text = $this->removeReply($text);

		return $text;
	}

	/**
	 * Converts line breaks to unix type.
	 * @param string $text
	 * @return string
	 */
	private function convertLineBreaks($text)
	{
		return str_replace("\r", '', $text);
	}

	/**
	 * Simple reply removal.
	 * @param string $text
	 * @return string
	 */
	private function removeReply($text)
	{
		return Strings::replace($text, self::REPLY, '');
	}

}
