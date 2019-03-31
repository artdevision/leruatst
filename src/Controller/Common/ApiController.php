<?php

namespace App\Controller\Common;

use App\Entity\Category;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends AbstractController
{

    public function index()
    {
        $this->getDoctrine()->getRepository(Category::class)->find();
    }
}