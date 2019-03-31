<?php

namespace App\DataFixtures;


use App\Entity\Category;
use App\Entity\Post;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class PostFixtures extends AppFixtures implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var CategoryRepository $catRepository */
        $catRepository = $manager->getRepository(Category::class);

        $categories = $catRepository->findAll();

        for($i = 0; $i < 100; $i++) {
            $post = new Post();
            /** @var PostRepository $repository */
            $repository = $manager->getRepository(Post::class);
            $repository->fill($post, [
                'title' =>  $this->faker->text(),
                'preview_text' => $this->faker->realText(),
                'text' => $this->faker->realText(),
                'author' => $this->faker->firstName(),
                'published' => $this->faker->boolean(),
                'published_at' => $this->faker->dateTime(),
            ]);

            shuffle($categories);
            $count = rand(1, 5);

            for($k = 0; $k < $count; $k++) {
                $post->addCategory($categories[$k]);
            }

            $manager->persist($post);
        }
        parent::load($manager);
    }

    public function getOrder()
    {
        return 2;
    }
}
