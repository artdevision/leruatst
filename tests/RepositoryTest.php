<?php

namespace App\Tests;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RepositoryTest extends KernelTestCase
{
    /**
     * @var Generator
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

        var_dump($category);
        $this->assertInstanceOf(Category::class, $category);
    }

    public function testUpdateCategory()
    {

    }

    public function testDeleteCategory()
    {

    }

    public function testCreatePost()
    {
        $this->assertTrue(true);
    }
}
