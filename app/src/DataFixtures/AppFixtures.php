<?php

/**
 * App fixtures.
 */

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class AppFixtures.
 *
 * Base application fixtures.
 */
class AppFixtures extends Fixture
{
    /**
     * Load data fixtures.
     *
     * @param ObjectManager $manager Object manager
     */
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
