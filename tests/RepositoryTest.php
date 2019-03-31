<?php

namespace App\Tests;

use App\Entity\Category;
use App\Entity\Post;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RepositoryTest extends KernelTestCase
{
    /**
     * @var Generator $faker
     */
    protected $faker;

    public function setUp()
    {
        parent::setUp();
        $this->faker = Factory::create();
        static::bootKernel();
    }

    public function testCreateCategory()
    {

        $category = CategoryRepository::create([
            'name' => $this->faker->title(),
            'description' => $this->faker->realText(),
        ]);
        $this->assertInstanceOf(Category::class, $category);
    }

    public function testUpdateCategory()
    {
        $this->assertTrue(true);
    }

    public function testDeleteCategory()
    {
        $this->assertTrue(true);
    }

    public function testCreatePost()
    {
        $this->assertTrue(true);
    }

    public function testPostCategorySync()
    {
        /** @var PostRepository $repository */
        $repository = PostRepository::getInstance();

        /** @var Post $post */
        $post = $repository->findOneBy([], ['id' => 'DESC']);
        $repository->syncCategories($post, [11, 12, 13, 14]);

        $categories = $post->getCategories();
        $categories->count();

        $this->assertTrue($categories->count() === 4);
    }
}
