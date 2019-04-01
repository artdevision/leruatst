<?php

namespace App\Repository;

use App\AppBundle\AppBundle;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;
use ReflectionParameter;
use ReflectionException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\OptimisticLockException;

class BaseRepository extends ServiceEntityRepository
{
    protected static $entity;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, static::$entity);
    }

    /**
     * @return BaseRepository
     */
    public static function getInstance()
    {
        $container = AppBundle::getContainer();

        /** @var ManagerRegistry $registry */
        $registry = $container->get('doctrine');

        /** @var static $repository */
        $repository = $registry->getRepository(static::$entity);

        return $repository;
    }

    /**
     * @param array $data
     * @return Category
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public static function create($data = []) {

        /** @var static $repository */
        $repository = self::getInstance();

        /** @var EntityManager $manager */
        $manager = $repository->getEntityManager();

        $entity = new static::$entity();

        $repository->fill($entity, $data);

        $manager->persist($entity);
        $manager->flush();
        $manager->refresh($entity);

        return $entity;
    }

    /**
     * @param $entity
     * @param array $data
     * @throws ReflectionException
     */
    public function fill(&$entity, array $data = []): void
    {
        foreach ($data as $key => $val) {
            $setter = "set" .  str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($entity, $setter)) {
                $param = new ReflectionParameter([$entity, $setter], 0);
                $type = $param->getType();
                switch ($type) {
                    case 'int':
                        $entity->$setter((int) $val);
                        break;
                    case 'bool':
                        $entity->$setter((bool) $val);
                        break;
                    case 'string':
                        $entity->$setter((string) $val);
                        break;
                    default:
                        $entity->$setter($val);
                        break;
                }
            }
        }
    }

    /**
     * @param int $p
     * @param int $perPage
     * @param array $orderBy
     * @return array
     * @throws NonUniqueResultException
     */
    public function paginateAll($p = 1, $perPage = 50, $orderBy = ['published_at' => 'DESC'])
    {
        $query = $this->createQueryBuilder('p');
        $totalQuery = $this->createQueryBuilder('p');
        if ($this instanceof PostRepository) {
            $query->where('p.published = true');
            $totalQuery->where('p.published = true');
        }
        $total = $totalQuery->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
        $result = $query->orderBy('p.' . key($orderBy), current($orderBy))
            ->setFirstResult($perPage * ($p - 1))
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
        return [
            'items' => $result,
            'total' => $total,
            'page' => $p,
            'perpage' => $perPage
        ];
    }
}
