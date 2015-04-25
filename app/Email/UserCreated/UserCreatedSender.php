<?php

namespace Email\UserCreated;

use Email\BaseEmail;
use Nette\Application\UI\ITemplateFactory;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

/**
 * Email sent when a new user is created.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class UserCreatedSender extends BaseEmail
{

	/** @var string */
	private $sender;

	/**
	 * @param ITemplateFactory $templateFactory
	 * @param IMailer $mailer
	 * @param string $sender
	 */
	public function __construct(ITemplateFactory $templateFactory, IMailer $mailer, $sender)
	{
		$this->sender = $sender;

		parent::__construct($templateFactory, $mailer);
	}

	/**
	 * Sends an email.
	 * @param string $token
	 * @param string $recipient
	 */
	public function send($token, $recipient)
	{
		$template = $this->templateFactory->createTemplate();
		$template->setFile(__DIR__ . '/userCreated.latte');
		$template->token = $token;

		$message = new Message;
		$message->addTo($recipient);
		$message->setFrom($this->sender);
		$message->setSubject('Nový uživatelský učet');
		$message->setHtmlBody($template);

		$this->mailer->send($message);
	}

}
