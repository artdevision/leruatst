<?php

namespace App\DataFixtures;


use App\Entity\Category;
use Doctrine\Common\Persistence\ObjectManager;

class CategoryFixtures extends AppFixtures
{

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 20; $i++) {

        }
        parent::load($manager);
    }
}