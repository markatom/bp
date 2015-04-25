<?php

namespace Tests\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Model\Entity\Accident;
use Model\Entity\Order;
use Model\Entity\OrderState;

/**
 * Orders fixtures.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class OrderFixtures extends AbstractFixture implements DependentFixtureInterface
{

	/**
	 * This method must return an array of fixtures classes
	 * on which the implementing class depends on
	 *
	 * @return array
	 */
	function getDependencies()
	{
		return [ClientFixtures::class, UserFixtures::class];
	}

	/**
	 * Load data fixtures with the passed EntityManager
	 *
	 * @param ObjectManager $manager
	 */
	public function load(ObjectManager $manager)
	{
		$manager->persist(
			$workAccident = new Order(
				'Pracovní úraz',
				new Accident(
					'Lhota - výrobní hala','2015-03-27',
					'Podlahový pás mi vtáhl nohu pod pás. Nebyl jsem poučen o bezpečnosti práce, koordinátor mě nutil '
						. 'podepsat bezpečnost práce po úraze, nepodepsal jsem to.',
					'mandant',
					'neuvedeno',
					'Utrpěl jsem zlomeniny palce s mírnou dislokací, zlomeninu prstu a zanártní kosti'
				),
				$this->getReference('user.johnDoe'),
				$this->getReference('client.karelKos')
			)
		);
		$workAccident->state->forceTransition(OrderState::POSTPONED);

		$manager->persist(
			$carAccident = new Order(
				'Dopravní nehoda',
				new Accident(
					'Na silnici I. třídy mezi obcemi Chlum - Petrovice',
					'2015-02-19',
					'V 14.30 hod. došlo k dopravní nehodě dvou vozidel, vinník dopravní nehody není znám, ujel '
						. 'z místa dopravní nehody. Poškozené vozidlo je firemní, majitelem je firma ZXZS s.r.o. '
						. 'Nehodu šetří policie Petrovice.',
					'neznámý',
					'není',
					'Zlomenina klíční kosti, pohmožděniny v oblasti hrudníku.'
				),
				$this->getReference('user.jamesSmith'),
				$this->getReference('client.petrZapalac'),
				$this->getReference('user.markMcDonald')
			)
		);

		$manager->persist(
			$schoolAccident = new Order(
				'Úraz ve škole',
				new Accident(
					'V tělocvičně základní školy Nová ves',
					'2015-03-02',
					'Má dcera hrála na hodině tělocviku basketbal. Při hře do ní omylem vrazila spolužačka. Má dcera '
						. 'dopadla na zem tak, že si poranila zápěstí rukou.',
					'spolužačka',
					'neuvedeno',
					'Pohmožděná zápěstí rukou.'
				),
				$this->getReference('user.jamesSmith'),
				$this->getReference('client.ondrejHubeny')
			)
		);
		$schoolAccident->state->forceTransition(OrderState::WAITING);

		$this->addReference('order.workAccident', $workAccident);
		$this->addReference('order.carAccident', $carAccident);
		$this->addReference('order.schoolAccident', $schoolAccident);

		$manager->flush();
	}

}
