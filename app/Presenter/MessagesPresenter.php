<?php

namespace Presenter;

use Email\UserCreated\ClientMessageSender;
use Model\Entity\Document;
use Model\Entity\IncomingMessage;
use Model\Entity\Order;
use Model\Entity\OutgoingMessage;
use Model\Entity\User;
use Model\Entity\Addressable;
use Model\Service\MessageFetcher;
use Nette\Http\IResponse;

/**
 * Messages resource controller.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class MessagesPresenter extends SecuredPresenter
{

	/** @var MessageFetcher @inject */
	public $messageFetcher;

	/** @var ClientMessageSender @inject */
	public $clientMessageSender;

	/**
	 * Creates a new message.
	 */
	public function actionCreate()
	{
		/** @var Order $order */
		$order = $this->em->getRepository(Order::class)->find($this->getQuery(['order', 'id']));

		if (!$order) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownOrder');
		}

		$documentIds = array_map(function ($document) {
			return $document['id'];
		}, $this->getPost('documents'));

		$documents = $this->em->getRepository(Document::class)->findBy(['id' => $documentIds]);

		if (count($documents) !== count($documentIds)) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownDocument');
		}

		$message = new OutgoingMessage($order, $this->getPost('subject'), $this->getPost('content'), $documents, $this->user, $order->client);

		$this->clientMessageSender->send($message);

		$this->em->persist($message)->flush();

		$this->sendJson(self::mapMessage($message));
	}

	/**
	 * Reads all messages.
	 */
	public function actionReadAll()
    {
		if ($this->getQuery('fetch', FALSE)) {
			if ($this->messageFetcher->fetchMessages() === 0) {
				$this->sendEmpty(IResponse::S304_NOT_MODIFIED);
			}
		}

		$criteria = ($orderId = $this->getQuery(['order', 'id'], FALSE))
			? ['order' => $orderId]
			: [];

		$messages = $this->em->getRepository(IncomingMessage::class)->findBy($criteria, ['createdAt' => 'DESC']);

		$this->sendJson(array_map([self::class, 'mapMessage'], $messages));
    }

	/**
	 * @param IncomingMessage $message
	 * @return array
	 */
	public static function mapMessage(IncomingMessage $message)
	{
		return [
			'type'      => $message instanceof OutgoingMessage ? 'outgoing' : 'incoming',
			'subject'   => $message->subject,
			'content'   => $message->content,
			'createdAt' => $message->createdAt->format(self::DATE_TIME_FORMAT),
			'sender'    => self::mapAddressable($message->sender),
			'recipient' => $message instanceof OutgoingMessage ? self::mapAddressable($message->recipient) : NULL,
			'documents' => array_map([DocumentsPresenter::class, 'mapDocument'], $message->documents),
		];
	}

	/**
	 * @param Addressable $addressable
	 * @return array
	 */
	private static function mapAddressable(Addressable $addressable)
	{
		return $addressable instanceof User
			? array_merge(UsersPresenter::mapUser($addressable), ['type' => 'user'])
			: array_merge(ClientsPresenter::mapClient($addressable), ['type' => 'client']);
	}

}
