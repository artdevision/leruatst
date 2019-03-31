<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Post;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\OptimisticLockException;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends BaseRepository
{
    protected static $entity = Post::class;

    /**
     * @param Post $entity
     * @param array $ids
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function syncCategories(Post &$entity, array $ids = [])
    {
        $manager = $this->getEntityManager();

        /** @var CategoryRepository $catRepository */
        $catRepository = $manager->getRepository(Category::class);
        $entity->resetCategories();

        if (count($ids)) {
            $categories = $catRepository->findBy(['id' => $ids]);

            foreach ($categories as $category) {
                $entity->addCategory($category);
            }
        }

        $manager->flush();
        $manager->refresh($entity);
    }

    // /**
    //  * @return Post[] Returns an array of Post objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
