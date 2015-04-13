<?php

namespace Tests\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Model\Entity\Role;
use Model\Entity\Session;
use Model\Entity\User;

/**
 * One admin account and two employee accounts.
 * One of admin and employee account is signed in.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class UserFixtures extends AbstractFixture
{

	/**
	 * Loads data fixtures with the passed EntityManager
	 * @param ObjectManager $manager
	 */
	public function load(ObjectManager $manager)
	{
		$manager->persist($employee = new Role('employee', 'Zaměstnanec'));
		$manager->persist($admin = new Role('admin', 'Administrátor'));

		$johnDoe = new User('John Doe', 'john.doe@example.net', $admin);
		$johnDoe->setPassword('foobar');
		$manager->persist($johnDoe);

		$jamesSmith = new User('James Smith', 'james.smith@example.net', $employee);
		$jamesSmith->setPassword('alpha');
		$manager->persist($jamesSmith);

		$markMcDonald = new User('Mark McDonald', 'mark.mcdonald@example.net', $employee);
		$markMcDonald->setPassword('lorem');
		$manager->persist($markMcDonald);

		$manager->persist(new Session($johnDoe, $longLife = FALSE));
		$manager->persist(new Session($jamesSmith, $longLife = FALSE));

		$manager->flush();
	}

}
