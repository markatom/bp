<?php

namespace Model\Service;

use greeny\MailLibrary\Attachment;
use greeny\MailLibrary\Connection;
use greeny\MailLibrary\Mail;
use Kdyby\Doctrine\EntityManager;
use LogicException;
use Model\Entity\Addressable;
use Model\Entity\Client;
use Model\Entity\Document;
use Model\Entity\IncomingMessage;
use Model\Entity\Order;
use Model\Entity\OrderState;
use Nette\Object;

/**
 * Manages incoming emails.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class MessageFetcher extends Object
{

	/** @var array [inbox => ..., accepted => ..., rejected => ...] */
	private $mailboxNames;

	/** @var EntityManager */
	private $em;

	/** @var Connection */
	private $imap;

	/** @var EmailNormalizer */
	private $emailNormalizer;

	/**
	 * @param array $mailboxNames
	 * @param EntityManager $em
	 * @param Connection $imap
	 * @param EmailNormalizer $htmlToPlain
	 */
    public function __construct(array $mailboxNames, EntityManager $em, Connection $imap, EmailNormalizer $htmlToPlain)
    {
		if (!isset($mailboxNames['inbox']) || !isset($mailboxNames['accepted']) || !isset($mailboxNames['rejected'])) {
			throw new LogicException('Please supply names of mailboxes (inbox, accepted and rejected).');
		}

		$this->mailboxNames = $mailboxNames;
		$this->em           = $em;
		$this->imap         = $imap;
		$this->emailNormalizer  = $htmlToPlain;
	}

	/**
	 * Fetches all messages from remote inbox via IMAP.
	 */
	public function fetchMessages()
	{
		$this->createMailboxes();

		$mails = $this->imap->getMailbox($this->mailboxNames['inbox'])->getMails();

		$mails->order(Mail::ORDER_DATE);

		foreach ($mails->fetchAll() as $mail) {
			$sender = $this->getAddressableByEmail($mail->getSender()->getEmail());

			if (!$sender instanceof Client
				|| !$order = $this->getOrderOfClient($sender)
			) {
				$this->reject($mail);
				continue;
			}

			$documents = array_map(function (Attachment $attachment) use ($order) {
				return new Document($attachment->getName(), $attachment->getType(), $attachment->getContent(), $order);
			}, $mail->getAttachments());

			$body = $mail->getTextBody();
			$body = $this->emailNormalizer->normalize($body);

			$this->em->persist($documents);
			$this->em->persist(new IncomingMessage($order, $mail->subject, $body, $documents, $sender));

			$this->accept($mail);
		}

		$this->em->flush();
		$this->imap->flush();
	}

	/**
	 * Creates mailboxes if they do not exist.
	 */
	private function createMailboxes()
	{
		$mailboxes = $this->imap->getMailboxes();

		foreach ($this->mailboxNames as $key => $name) {
			if (!isset($mailboxes[$name])) {
				$this->imap->createMailbox($name);
			}
		}
	}

	/**
	 * Returns addressable entity by given email address.
	 * @param string $email
	 * @return Addressable|NULL
	 */
	private function getAddressableByEmail($email)
	{
		return $this->em->getRepository(Addressable::class)->findOneBy(['email' => $email]);
	}

	/**
	 * Returns order of given client if exactly one is active.
	 * @param Client $client
	 * @return Order|NULL
	 */
	private function getOrderOfClient(Client $client)
	{
		$orders = $this->em->createQuery(
			sprintf("
				SELECT o
				FROM %s o
				WHERE o.client = ?0
					AND o.state.slug NOT IN ('%s', '%s')
			", Order::class, OrderState::COMPLETED, OrderState::CANCELLED)
		)->setParameters([$client])->getResult();

		if (!$orders || count($orders) > 1) {
			return NULL; // no active order or multiple active orders for given client
		}

		return $orders[0];
	}

	/**
	 * Accept incoming email.
	 * @param Mail $mail
	 */
	private function accept(Mail $mail)
	{
		$mail->setFlags([Mail::FLAG_SEEN => TRUE]);
		$mail->move($this->mailboxNames['accepted']);
	}

	/**
	 * Reject incoming email.
	 * @param Mail $mail
	 */
	private function reject(Mail $mail)
	{
		$mail->move($this->mailboxNames['rejected']);
	}

}
