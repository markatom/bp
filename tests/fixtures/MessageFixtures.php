<?php

namespace Tests\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Model\Entity\IncomingMessage;
use Model\Entity\OutgoingMessage;

/**
 * Messages fixtures.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class MessageFixtures extends AbstractFixture implements DependentFixtureInterface
{

	/**
	 * This method must return an array of fixtures classes
	 * on which the implementing class depends on
	 *
	 * @return array
	 */
	function getDependencies()
	{
		return [UserFixtures::class, ClientFixtures::class, OrderFixtures::class];
	}

	/**
	 * Load data fixtures with the passed EntityManager
	 *
	 * @param ObjectManager $manager
	 */
	public function load(ObjectManager $manager)
	{
		$manager->persist(
			new OutgoingMessage(
				$this->getReference('order.carAccident'), 'Morbi et tempus lacus', "Vážený pane Zapalači,\n\n"
					. 'Vestibulum odio lectus, pretium sit amet commodo ut, cursus quis magna. Vestibulum tincidunt '
					. 'quam magna, sit amet malesuada neque lobortis et. Nulla facilisi. Integer non quam iaculis, '
					. 'bibendum sem eu, pellentesque odio. Nam a sollicitudin ante, ac hendrerit ante. Mauris pulvinar '
					. 'odio et augue viverra, sed egestas massa mollis. Curabitur.', [],
				$this->getReference('user.markMcDonald'), $this->getReference('client.petrZapalac')
			)
		);

		$manager->persist(
			new IncomingMessage(
				$this->getReference('order.carAccident'), 'Proin aliquam, felis egestas semper', "Dobrý den,\n\n"
					. 'Pellentesque feugiat mauris et tristique ultrices. Sed non pulvinar urna. Pellentesque '
					. 'venenatis nisi id orci rhoncus volutpat. Aliquam eget erat faucibus, semper nunc vitae, '
					. 'efficitur est. Vestibulum nec aliquet ipsum. Pellentesque congue, lacus sed convallis commodo, '
					. 'velit dolor fermentum mi, quis sollicitudin magna diam volutpat diam. Ut nisl justo, dictum.'
					. "\n\nS pozdravem\nPetr Zapalač", [], $this->getReference('client.petrZapalac')
			)
		);

		$manager->flush();
	}

}
