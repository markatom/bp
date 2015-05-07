<?php

namespace Model\Service;

use Nette\Object;
use Nette\Utils\Strings;

/**
 * Simple HTML to plain text converter.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class HtmlToPlain extends Object
{

	/** @var string[] */
	private static $wrap = [
		'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'li', 'p', 'br'
	];

	/**
	 * @param string $text
	 * @return string
	 */
	public function convert($text)
	{
		$text = Strings::replace($text, '~\s+~', ' ');

		foreach (self::$wrap as $tag) {
			$text = Strings::replace($text, "~</$tag>~", "\n");
		}

		return Strings::replace($text, '~<[^>]*>~', '');
    }

}
