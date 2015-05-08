<?php

namespace Document\Poa;

use DateTime;
use Document\BaseGenerator;
use Document\MissingMandatoryItemsException;
use Model\Entity\Order;
use mPDF;

/**
 * Power of attorney document generator.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class PoaGenerator extends BaseGenerator
{

	/**
	 * Generates document for given order.
	 * @param Order $order
	 * @return string
	 */
	function generate(Order $order)
	{
		$missing = $this->missing([
			'datum narození klienta' => $order->client->dateOfBirth,
			'ulice klienta'          => $order->client->address->street,
			'město klienta'          => $order->client->address->city,
			'PSČ klienta'            => $order->client->address->zip
		]);

		if ($missing) {
			throw new MissingMandatoryItemsException($missing);
		}

		$template = $this->templateFactory->createTemplate();
		$template->setFile(__DIR__ . '/poa.latte');
		$template->order = $order;
		$template->today = new DateTime;

		$pdf = new mPDF;
		$pdf->WriteHTML((string) $template);

		return $pdf->Output('', 'S'); // empty file name, 'S' for return as string
	}

}
