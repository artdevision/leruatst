<?php

namespace App\DataFixtures;


use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CategoryFixtures extends AppFixtures implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 20; $i++) {
            $entity = new Category();

            /** @var CategoryRepository $repository */
            $repository = $manager->getRepository(Category::class);

            $repository->fill($entity, [
                'name' => $this->faker->text(),
                'description' => $this->faker->realText()
            ]);

            $manager->persist($entity);
        }
        parent::load($manager);
    }

    public function getOrder()
    {
        return 1;
    }
}
