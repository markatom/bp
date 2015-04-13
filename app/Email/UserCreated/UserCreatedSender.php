<?php

namespace Email\UserCreated;

use Email\BaseEmail;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

/**
 * Email sent when a new user is created.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class UserCreatedSender extends BaseEmail
{

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
		$message->setSubject('Nový uživatelský učet');
		$message->setHtmlBody($template);

		$this->mailer->send($message);
	}

}
