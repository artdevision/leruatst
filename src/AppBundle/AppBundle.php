<?php


namespace App\AppBundle;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;


class AppBundle extends Bundle
{
    private static $containerInstance = null;

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        self::$containerInstance = $container;
    }

    /**
     * @return ContainerInterface|null
     */
    public static function getContainer()
    {
        return self::$containerInstance;
    }

}