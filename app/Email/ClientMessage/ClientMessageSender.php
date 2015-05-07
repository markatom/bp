<?php

namespace Email\UserCreated;

use Email\BaseEmail;
use Model\Entity\OutgoingMessage;
use Nette\Application\UI\ITemplateFactory;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

/**
 * Email sent when a new message for client is created.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class ClientMessageSender extends BaseEmail
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
	 * @param OutgoingMessage $message
	 */
	public function send(OutgoingMessage $message)
	{
		$template = $this->templateFactory->createTemplate();
		$template->setFile(__DIR__ . '/clientMessage.latte');
		$template->user    = $message->sender;
		$template->content = $message->content;

		$mail = new Message;
		$mail->addTo($message->recipient->email);
		$mail->setFrom($this->sender);
		$mail->setSubject($message->subject);
		$mail->setHtmlBody($template);

		foreach ($message->documents as $document) {
			$mail->addAttachment($document->name, stream_get_contents($document->data), $document->type);
		}

		$this->mailer->send($mail);
	}

}
