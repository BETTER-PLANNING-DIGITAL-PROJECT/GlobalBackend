<?php

namespace App\Serializer;

use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use App\Service\FileUploader;
use App\Entity\School\Schooling\Registration\Student;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class StudentNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{

    private FileUploader $fileUploader;
    private const ALREADY_CALLED = 'SUPERHEROES_OBJECT_NORMALIZER_ALREADY_CALLED';
    private $normalizer;

    public function __construct(FileUploader $fileUploader) {
        $this->fileUploader = $fileUploader;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool {
        return !isset($context[self::ALREADY_CALLED]) && $data instanceof Student;
    }

    public function normalize($object, ?string $format = null, array $context = []) {
        $context[self::ALREADY_CALLED] = true;

        // update the picture with the url
        $object->setPicture($this->fileUploader->getUrl($object->getPicture()));
        return $this->normalizer->normalize($object, $format, $context);

    }

    public function setNormalizer(NormalizerInterface $normalizer)
    {
        // TODO: Implement setNormalizer() method.
        $this->normalizer = $normalizer;
    }
}