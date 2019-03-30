<?php

namespace App\DataFixtures;


use Doctrine\Common\Persistence\ObjectManager;

class PostFixtures extends AppFixtures
{
    public function load(ObjectManager $manager)
    {

        parent::load($manager);
    }
}