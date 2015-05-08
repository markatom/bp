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

}
