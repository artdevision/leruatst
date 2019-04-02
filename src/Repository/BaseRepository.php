<?php

namespace App\Repository;

use App\AppBundle\AppBundle;
use App\Entity\Category;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
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
     * @return Category|Post
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

    public function save(&$entity, bool $new = false)
    {
        if ($new === true) {
            $this->getEntityManager()->persist($entity);
        }
        $this->getEntityManager()->flush();
        $this->getEntityManager()->refresh($entity);
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
     * @param null $condition
     * @return array
     * @throws NonUniqueResultException
     */
    public function paginateAll($p = 1, $perPage = 50, $orderBy = ['published_at' => 'DESC'], $condition = null)
    {
        $query = $this->createQueryBuilder('p');
        $totalQuery = $this->createQueryBuilder('p');

        if ($this instanceof PostRepository) {
            $query->where('p.published = true');
            $totalQuery->where('p.published = true');
        }

        if (!is_null($condition)) {
            $query->andWhere($condition);
            $totalQuery->andWhere($condition);
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

    /**
     * @param $id
     * @return mixed|null
     * @throws DBALException
     */
    public function destroy($id)
    {
        if(is_int($id) || is_array($id)) {
            if ($this instanceof PostRepository || $this instanceof CategoryRepository)
            {
                $field = ($this instanceof PostRepository) ? 'post_id' : 'category_id';
                $connection = $this->getEntityManager()->getConnection();
                $queryBuilder = $connection->createQueryBuilder();
                $query = $queryBuilder->delete('posts_categories', 'p')
                    ->where($queryBuilder->expr()->in('p.' . $field, is_int($id) ? [$id] : $id))
                    ->getSQL();
                $connection->exec($query);
            }
            return $this->createQueryBuilder('p')
                ->delete()
                ->where((new Expr())->in('p.id', is_int($id) ? [$id] : $id))
                ->getQuery()
                ->execute();
        }
        return null;
    }
}
