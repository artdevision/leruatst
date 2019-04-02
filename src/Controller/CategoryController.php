<?php

namespace App\Controller;

use App\Controller\Common\ApiController;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Serializer\Normalizer\CategoryListNormalizer;
use App\Serializer\Normalizer\CategoryNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\DBALException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api")
 */
class CategoryController extends ApiController
{
    /**
     * @var CategoryRepository
     */
    protected $repository;

    /**
     * @return CategoryRepository
     */
    public function getRepository()
    {
        if(is_null($this->repository)) {
            $this->repository = $this->getDoctrine()->getRepository(Category::class);
        }
        return $this->repository;
    }

    /**
     * @param CategoryListNormalizer $normalizer
     * @return Response
     * @throws NonUniqueResultException
     * @throws ExceptionInterface
     * @Route("/category", methods={"GET"})
     */
    public function index(CategoryListNormalizer $normalizer)
    {
        $request = $this->getRequest();

        $categories = $this->getRepository()->paginateAll(
            (int) $request->get('p', 1),
            (int) $request->get('perpage', 50),
            ['created_at' => 'DESC']
        );

        $categories = $normalizer->normalize($categories);

        return $this->sendResponse($this->getSerializer()->serialize($categories, 'json'));
    }

    /**
     * @param int $id
     * @param CategoryNormalizer $normalizer
     * @return Response
     * @throws ExceptionInterface
     * @Route("/category/{id<\d+>}", methods={"GET"})
     */
    public function view(int $id, CategoryNormalizer $normalizer)
    {
        $category = $this->getRepository()->find($id);

        if ($category === null) {
            throw new BadRequestHttpException("Неверный ID");
        }

        $category = $normalizer->normalize($category);

        return $this->sendResponse($this->getSerializer()->serialize($category, 'json'));
    }

    /**
     * @param CategoryNormalizer $normalizer
     * @param ValidatorInterface $validator
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response
     * @throws ExceptionInterface
     * @throws \ReflectionException
     * @Route("/category/create", methods={"POST", "PUT"})
     */
    public function create(CategoryNormalizer $normalizer, ValidatorInterface $validator)
    {
        $data = $this->getData();
        $repository = $this->getRepository();

        $category = new Category();
        $repository->fill($category, $data);

        $errors = $validator->validate($category);
        if ($errors->count()) {
            return $this->responseValidationErrors($errors);
        }

        $repository->save($category, true);

        $category = $normalizer->normalize($category);
        return $this->sendResponse($this->getSerializer()->serialize($category, 'json'));
    }

    /**
     * @param int $id
     * @param CategoryNormalizer $normalizer
     * @param ValidatorInterface $validator
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response
     * @throws ExceptionInterface
     * @throws \ReflectionException
     * @Route("/category/update/{id<\d+>?0}", defaults={"id"=0}, methods={"POST", "PUT"})
     */
    public function update(int $id, CategoryNormalizer $normalizer, ValidatorInterface $validator)
    {
        $data = $this->getData();

        $repository = $this->getRepository();

        if(
            ($id === 0 && !$this->isJson) ||
            ($id === 0 && !isset($data['id'])) ||
            !($category = $repository->find(($id !== 0) ? $id : $data['id']))
        ) {
            throw new BadRequestHttpException("Неверный ID или JSON");
        }

        $repository->fill($category, $data);
        $errors = $validator->validate($category);

        if ($errors->count()) {
            return $this->responseValidationErrors($errors);
        }

        $repository->save($category);
        $category = $normalizer->normalize($category);

        return $this->sendResponse($this->getSerializer()->serialize($category, 'json'));
    }

    /**
     * @param int $id
     * @return JsonResponse
     * @throws DBALException
     * @Route("/category/destroy/{id<\d+>?0}", defaults={"id"=0}, methods={"GET", "POST", "DELETE"})
     */
    public function destroy(int $id)
    {
        $data = $this->getData();

        if(
            ($id === 0 && $data === null) ||
            ($id === 0 && !isset($data['id']))

        ) {
            throw new BadRequestHttpException("Неверный ID или JSON");
        }

        if($count = $this->getRepository()->destroy(($id !== 0) ? $id : $data['id'])) {
            return new JsonResponse([
                'success' => true,
                'count' => $count,
            ]);
        }

        return new JsonResponse([
            'error' => true,
            'message' => 'Ошибка удаления'
        ], 400);
    }
}
