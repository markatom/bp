<?php

namespace Tests\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Model\Entity\Document;

/**
 * Documents fixtures.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class DocumentFixtures extends AbstractFixture implements DependentFixtureInterface
{

	/**
	 * This method must return an array of fixtures classes
	 * on which the implementing class depends on
	 *
	 * @return array
	 */
	function getDependencies()
	{
		return [OrderFixtures::class];
	}

	/**
	 * Load data fixtures with the passed EntityManager
	 *
	 * @param ObjectManager $manager
	 */
	public function load(ObjectManager $manager)
	{
		$manager->persist($document = new Document('img.jpg', 'image/jpeg', file_get_contents(__DIR__ . '/img.jpg'),
			$this->getReference('order.carAccident')));

		$this->addReference('document.img', $document);

		$manager->flush();
	}

}