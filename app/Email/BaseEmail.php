<?php

namespace Email;

use Nette\Application\UI\ITemplateFactory;
use Nette\Mail\IMailer;
use Nette\Object;

/**
 * Base class for emails.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
abstract class BaseEmail extends Object
{

	/** @var ITemplateFactory */
	protected $templateFactory;

	/** @var IMailer */
	protected $mailer;

	/**
	 * @param ITemplateFactory $templateFactory
	 * @param IMailer $mailer
	 */
	public function __construct(ITemplateFactory $templateFactory, IMailer $mailer)
	{
		$this->templateFactory = $templateFactory;
		$this->mailer          = $mailer;
	}

}