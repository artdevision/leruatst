<?php

namespace App\Controller;

use App\Controller\Common\ApiController;
use App\Entity\Post;
use App\Repository\PostRepository;
use App\Serializer\Normalizer\PostListNormalizer;
use App\Serializer\Normalizer\PostNormalizer;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

        $condition = null;

        /**
         * Если в теле JSON {"category": {"id": 123}} или {"category": {"id": [123, 124]}}
         * Применим фильтр по категрориям
         */
        if ($this->getRequest()->getMethod() === "POST") {
            $data = $this->getData();
            if (isset($data['category']) && isset($data['category']['id'])) {
                $ids = $this->getRepository()->getPostIdsByCategory(is_array($data['category']['id']) ? $data['category']['id'] : [$data['category']['id']]);
                $condition = count($ids) ? (new Expr())->in("p.id", $ids) : null;
            }
        }

        $posts = $this->getRepository()->paginateAll(
            (int) $request->get('p', 1),
            (int) $request->get('perpage', 50),
            ['published_at' => 'DESC'],
            $condition
        );

        $posts = $normaliser->normalize($posts);

        return $this->sendResponse($this->getSerializer()->serialize($posts, 'json'));
    }

    /**
     * @param int $id
     * @param PostNormalizer $normalizer
     * @return Response
     * @throws ExceptionInterface
     * @Route("/post/{id<\d+>}", methods={"GET"})
     */
    public function view(int $id, PostNormalizer $normalizer)
    {
        $post = $this->getRepository()->find($id);

        if ($post === null) {
            throw new BadRequestHttpException("Неверный ID");
        }

        $post = $normalizer->normalize($post);

        return $this->sendResponse($this->getSerializer()->serialize($post, 'json'));
    }

    /**
     * @param PostNormalizer $normalizer
     * @param ValidatorInterface $validator
     * @return Response|void
     * @throws ExceptionInterface
     * @throws \ReflectionException
     * @Route("/post/create", methods={"POST", "PUT"})
     */
    public function create(PostNormalizer $normalizer, ValidatorInterface $validator)
    {
        $data = $this->getData();
        $repository = $this->getRepository();

        $post = new Post();

        $repository->fill($post, $data);
        $errors = $validator->validate($post);

        if ($errors->count()) {
            return $this->responseValidationErrors($errors);
        }

        $repository->save($post, true);

        $post = $normalizer->normalize($post);
        return $this->sendResponse($this->getSerializer()->serialize($post, 'json'));
    }

    /**
     * @param int $id
     * @param PostNormalizer $normalizer
     * @param ValidatorInterface $validator
     * @return Response|void
     * @throws ExceptionInterface
     * @throws \ReflectionException
     * @Route("/post/update/{id<\d+>?0}", defaults={"id"=0}, methods={"POST", "PUT"})
     */
    public function update(int $id, PostNormalizer $normalizer, ValidatorInterface $validator)
    {
        $data = $this->getData();

        /** @var PostRepository $repository */
        $repository = $this->getRepository();

        if(
            ($id === 0 && !$this->isJson) ||
            ($id === 0 && !isset($data['id'])) ||
            !($post = $repository->find(($id !== 0) ? $id : $data['id']))
        ) {
            throw new BadRequestHttpException("Неверный ID или JSON");
        }

        $repository->fill($post, $data);
        $errors = $validator->validate($post,null,['update']);

        if ($errors->count()) {
            return $this->responseValidationErrors($errors);
        }

        $repository->save($post);

        $post = $normalizer->normalize($post);

        return $this->sendResponse($this->getSerializer()->serialize($post, 'json'));
    }

    /**
     * @Route("/post/destroy/{id<\d+>?0}", defaults={"id"=0}, methods={"GET", "POST", "DELETE"})
     */
    public function destroy(int $id)
    {
        $data = $this->getData();

        if(($id === 0 && !$this->isJson)) {
            throw new BadRequestHttpException("Неверный ID или JSON");
        }
    }
}
