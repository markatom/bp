<?php

namespace Document;

use Nette\Application\UI\ITemplateFactory;
use Nette\Object;

/**
 * Base class for generators.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
abstract class BaseGenerator extends Object implements Generator
{

	/** @var ITemplateFactory */
	protected $templateFactory;

	/**
	 * @param ITemplateFactory $templateFactory
	 */
	public function __construct(ITemplateFactory $templateFactory)
	{
		$this->templateFactory = $templateFactory;
	}

	/**
	 * Returns missing items.
	 * @param array $items [name => value]
	 * @return array An array of names.
	 */
	public function missing(array $items)
	{
		return array_keys(array_filter($items, function ($item) {
			return !$item;
		}));
	}

}
