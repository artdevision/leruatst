<?php

namespace App\Controller\Common;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var bool
     */
    protected $isJson = false;

    /**
     * @var array|mixed|null
     */
    protected $data;

    public function getSerializer()
    {
        if(is_null($this->serializer)) {
            $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        }
        return $this->serializer;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        if (is_null($this->request)) {
            $this->request = Request::createFromGlobals();
        }
        return $this->request;
    }

    /**
     * @return array|mixed|null
     */
    public function getData()
    {
        $request = $this->getRequest();
        if (in_array($request->getMethod(), ["POST", "PUT", "DELETE"]) && is_null($this->data)) {
            if($this->data = json_decode($request->getContent(), true)) {
                $this->isJson = true;
            }
            else {
                $this->data = $request->request->all();
            }
        }

        return $this->data;
    }

    /**
     * @param ConstraintViolationList $errors
     * @return JsonResponse
     */
    public function responseValidationErrors(ConstraintViolationList $errors)
    {
        $response = [
            'error' => true,
            'type' => 'validation',
            'errors' => array_map(function(ConstraintViolationInterface $error) {
                return [$error->getPropertyPath() => $error->getMessage()];
            }, $errors->getIterator()->getArrayCopy())
        ];
        return new JsonResponse($response, 400);
    }

    /**
     * @param string $content
     * @param int $status
     * @return Response
     */
    public function sendResponse($content = '', $status = 200)
    {
        return new Response($content, $status, [
            'Content-Type' => 'application/json'
        ]);
    }
}
