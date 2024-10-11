<?php

namespace App\Serializer;

use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use App\Service\UserFileUploader;
use App\Entity\Security\User;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class UserNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{

    private UserFileUploader $fileUploader;
    private const ALREADY_CALLED = 'SUPERHEROES_OBJECT_NORMALIZER_ALREADY_CALLED';
    private $normalizer;

    public function __construct(UserFileUploader $fileUploader) {
        $this->fileUploader = $fileUploader;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool {
        return !isset($context[self::ALREADY_CALLED]) && $data instanceof User;
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
