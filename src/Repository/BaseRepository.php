<?php

namespace App\Repository;

use App\AppBundle\AppBundle;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\RegistryInterface;

class BaseRepository extends ServiceEntityRepository
{
    protected static $entity;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, static::$entity);
    }

    /**
     * @param array $data
     * @return Category
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \ReflectionException
     */
    public static function create($data = []) {

        $container = AppBundle::getContainer();

        /** @var EntityManager $manager */
        $manager = $container->get('doctrine')->getManager();

        $entity = new static::$entity();
        foreach ($data as $key => $val) {
            $setter = "set" .  str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($entity, $setter)) {
                $param = new \ReflectionParameter([static::$entity, $setter], 0);
                $type = $param->getType();
                switch ($type) {
                    case 'int':
                        $entity->$setter((int) $val);
                        break;
                    default:
                    case 'string':
                        $entity->$setter((string) $val);
                    break;
                }
            }
        }

        $manager->persist($entity);
        $manager->flush();
        $manager->refresh($entity);

        return $entity;
    }
}
