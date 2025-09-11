<?php

/**
 * URL fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Tag;
use App\Entity\Url;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Random\RandomException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Class UrlFixtures.
 */
class UrlFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Constructor.
     *
     * @param string $baseUrl Base URL
     */
    public function __construct(#[Autowire('%app.base_url%')] private readonly string $baseUrl)
    {
    }

    /**
     * Get dependencies.
     *
     * @return array<class-string> Fixture classes
     */
    public function getDependencies(): array
    {
        return [UserFixtures::class, TagFixtures::class];
    }

    /**
     * Load data.
     *
     * @throws RandomException
     */
    protected function loadData(): void
    {
        if (!$this->manager instanceof ObjectManager || !$this->faker instanceof Generator) {
            return;
        }

        $this->createMany(50, 'urls', function () {
            $url = new Url();
            $url->setOriginalUrl($this->faker->url());

            $slug = bin2hex(random_bytes(3));
            $url->setShortenedUrl(rtrim($this->baseUrl, '/').'/'.$slug);

            $url->setCreatedAt(\DateTimeImmutable::createFromMutable(
                $this->faker->dateTimeBetween('-100 days', '-1 days')
            ));

            /** @var User $user */
            $user = $this->getRandomReference('users');
            $url->setUser($user);
            $url->setEmail($user->getEmail());

            for ($j = 0; $j < random_int(1, 4); ++$j) {
                /** @var Tag $tag */
                $tag = $this->getRandomReference('tags');
                $url->addTag($tag);
            }

            $url->setClicks(random_int(1, 142));

            return $url;
        });

        $this->manager->flush();
    }
}
