<?php

namespace Tests\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Model\Entity\Address;
use Model\Entity\Client;

/**
 * Clients fixtures.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class ClientFixtures extends AbstractFixture
{

	/**
	 * Load data fixtures with the passed EntityManager
	 *
	 * @param ObjectManager $manager
	 */
	public function load(ObjectManager $manager)
	{
		$manager->persist($karelKos = new Client('Karel Kos', '1973-11-02', 'kosik@seznam.cz', '732049293',
			new Address('Liborova 552', 'Praha', '16900', 'Česká republika')));

		$manager->persist($petrZapalac = new Client('Petr Zapalač', '1962-07-28', 'p.zaplac@gmail.com', '604395382',
			new Address('Kapitána Jaroše 349', 'Teplice', '34552', 'Česká republika')));

		$manager->persist($ondrejHubeny = new Client('Ondřej Hubený', '1985-12-27', 'ondrej.hubeny@centrum.cz', '777394827',
			new Address('Svatovítská 34', 'Vysoké Mýto', '48442', 'Česká republika')));

		$manager->persist(new Client('Kryštof Čalfa', '1977-09-04', 'calfa.k@gmail.com', '732049872',
			new Address('Jindřišská 13', 'Brno', '24824', 'Česká republika')));

		$manager->persist(new Client('Zdeněk Velínský', '1975-11-21', 'velty4@seznam.cz', '666428293',
			new Address('Na Letišti 2', 'Znojmo', '13994', 'Česká republika')));

		$manager->persist(new Client('Adam Němec', '1965-02-14', 'adam.nemec@gmail.com', '602593829',
			new Address('Obránců 425', 'Plzeň', '13442', 'Česká republika')));

		$manager->persist(new Client('Jiří Hojek', '1991-02-22', 'jiri@hojek.cz', '743952857',
			new Address('Pod Radnicí 23', 'Jihlava', '13494', 'Česká republika')));

		$manager->persist(new Client('Tomáš Jonáš', '1982-05-11', 'tom.jonas@centrum.cz', '723483773',
			new Address('Kostelecká 22', 'Úštěk', '24343', 'Česká republika')));

		$this->addReference('client.karelKos', $karelKos);
		$this->addReference('client.petrZapalac', $petrZapalac);
		$this->addReference('client.ondrejHubeny', $ondrejHubeny);

		$manager->flush();
	}

}