<?php

namespace App\Controller;

use App\Controller\Common\ApiController;
use App\Entity\Post;
use App\Repository\PostRepository;
use App\Serializer\Normalizer\PostListNormalizer;
use App\Serializer\Normalizer\PostNormalizer;
use Doctrine\ORM\NonUniqueResultException;
use http\Client\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class PostController extends ApiController
{
    /**
     * @var PostRepository
     */
    protected $repository;

    public function getRepository()
    {
        if(is_null($this->repository)) {
            $this->repository = $this->getDoctrine()->getRepository(Post::class);
        }
        return $this->repository;
    }

    /**
     * @param PostListNormalizer $normaliser
     * @return Response
     * @throws ExceptionInterface
     * @throws NonUniqueResultException
     * @Route("/post", methods={"GET", "POST"})
     */
    public function index(PostListNormalizer $normaliser)
    {
        $request = $this->getRequest();

        $posts = $this->getRepository()->paginateAll((int) $request->get('p', 1), (int) $request->get('perpage', 50));

        $posts = $normaliser->normalize($posts);

        return new Response($this->getSerializer()->serialize($posts, 'json'), 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/post/{id}", methods={"GET", "POST", "PUT"})
     */
    public function view($id, PostNormalizer $normalizer)
    {

    }

    /**
     * @Route("/post/update/{id}", methods={"POST", "PUT"})
     */
    public function update($id, PostNormalizer $normalizer)
    {

    }

    /**
     * @Route("/post/destroy/{id}", methods={"GET", "DELETE"})
     */
    public function destroy($id)
    {

    }
}
