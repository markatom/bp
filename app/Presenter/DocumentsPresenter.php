<?php

namespace Presenter;

use DateTime;
use Document\Poa\PoaGenerator;
use Latte\Runtime\Filters;
use Model\Entity\Document;
use Model\Entity\Order;
use Model\Entity\Token;
use Model\Service\Tokens;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\Http\FileUpload;
use Nette\Http\IResponse;

/**
 * Documents resource controller.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class DocumentsPresenter extends SecuredPresenter
{

	/** @var Tokens @inject */
	public $tokens;

	/** @var PoaGenerator @inject */
	public $poaGenerator;

	/**
	 * Downloading of image is performed with token.
	 */
	public function startup()
	{
		if ($this->action === 'read') {
			ApiPresenter::startup(); // skip authentication

		} else {
			parent::startup();
		}
	}

	/**
	 * Creates document from upload or generates document from template.
	 */
	public function actionCreate()
	{
		$order = $this->em->getRepository(Order::class)->find($this->getQuery(['order', 'id']));

		if (!$order) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownOrder');
		}

		$generate = $this->getQuery('generate', NULL);
		if ($generate === 'poa') {
			$content  = $this->poaGenerator->generate($order);
			$document = new Document('plna-moc.pdf', Document::TYPE_PDF, $content, $order);

		} else {
			/** @var FileUpload[] $files */
			$files = $this->getHttpRequest()->getFiles();

			if (!isset($files['file'])) {
				$this->sendError(IResponse::S400_BAD_REQUEST, 'missingFile');
			}

			$file = $files['file'];

			if (!$file->isOk()) {
				$this->sendError(IResponse::S500_INTERNAL_SERVER_ERROR, 'uploadFailed');
			}

			$document = new Document($file->getName(), $file->getContentType(), $file->getContents(), $order);
		}

		$this->em->persist($document)->flush();

		$this->sendJson(self::mapDocument($document));
	}

	/**
	 * Reads all documents.
	 */
	public function actionReadAll()
	{
		$orderId = $this->getQuery(['order', 'id']);

		$documents = $this->em->getRepository(Document::class)->findBy(['order' => $orderId], ['createdAt' => 'DESC']);

		$this->sendJson(array_map([self::class, 'mapDocument'], $documents));
	}

	/**
	 * Reads single file entity.
	 * @param int $id
	 * @throws BadRequestException
	 */
	public function actionRead($id)
	{
		$document = $this->em->getRepository(Document::class)->find($id);

		if (!$document) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownDocument');
		}

		if ($this->getQuery('download', FALSE)) {
			if ($key = $this->getQuery('token', NULL)) {
				if (!$token = $this->tokens->get($key, 'downloadImage')) {
					throw new BadRequestException;
				}

				$data = stream_get_contents($document->data);

				header("Content-Type: $document->type");
				header('Content-Disposition: attachment; filename="' . $document->name . '"; filename*=utf-8\'\'' . rawurlencode($document->name));
				header("Content-Length: " . strlen($data));

				echo $data;

				$this->terminate();

			} else {
				$this->authenticate();

				$this->sendJson([
					'token' => [
						'key' => $this->tokens->create($this->user, 'downloadImage', '+10 min')->key,
					],
				]);
			}

		} else {
			$this->authenticate();

			$this->sendJson(self::mapDocument($document));
		}
	}

	/**
	 * @param Document $document
	 * @return array
	 */
	public static function mapDocument(Document $document)
	{
		return [
			'id'            => $document->id,
			'name'          => $document->name,
			'type'          => $document->type,
			'size'          => $size = strlen(stream_get_contents($document->data)),
			'formattedSize' => str_replace('.', ',', Filters::bytes($size)),
			'createdAt'     => $document->createdAt->format(self::DATE_TIME_FORMAT)
		];
    }

}
