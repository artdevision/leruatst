<?php

namespace App\Serializer\Normalizer;

use App\Entity\Category;
use App\Entity\Post;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;


class PostNormalizer implements NormalizerInterface
{

    private $normalizer;

    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * Normalizes an object into a set of arrays/scalars.
     *
     * @param Post $object Object to normalize
     * @param string $format Format the normalization result will be encoded as
     * @param array $context Context options for the normalizer
     *
     * @return array|string|int|float|bool
     *
     * @throws InvalidArgumentException   Occurs when the object given is not an attempted type for the normalizer
     * @throws CircularReferenceException Occurs when the normalizer detects a circular reference when no circular
     *                                    reference handler can fix it
     * @throws LogicException             Occurs when the normalizer is not called in an expected context
     * @throws ExceptionInterface         Occurs for all the other cases of errors
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'id' => $object->getId(),
            'title' => $object->getTitle(),
            'author' => $object->getAuthor(),
            'published_at' => $object->getPublishedAt()->getTimestamp(),
            'created_at' => $object->getCreatedAt()->getTimestamp(),
            'updated_at' => $object->getUpdatedAt()->getTimestamp(),
            'preview_text' => $object->getPreviewText(),
            'text' => $object->getText(),
            'categories' => array_map(function(Category $category) {
                return [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                ];
            }, $object->getCategories()->toArray())
        ];
    }

    /**
     * Checks whether the given class is supported for normalization by this normalizer.
     *
     * @param mixed $data Data to normalize
     * @param string $format The format being (de-)serialized from or into
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        // TODO: Implement supportsNormalization() method.
    }
}
