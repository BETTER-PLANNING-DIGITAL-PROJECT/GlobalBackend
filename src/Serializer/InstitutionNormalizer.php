<?php

namespace App\Serializer;

use App\Entity\Security\Institution;
use App\Service\InstitutionFileUploader;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class InstitutionNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{

    private InstitutionFileUploader $fileUploader;
    private const ALREADY_CALLED = 'SUPERHEROES_OBJECT_NORMALIZER_ALREADY_CALLED';
    private $normalizer;

    public function __construct(InstitutionFileUploader $fileUploader) {
        $this->fileUploader = $fileUploader;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool {
        return !isset($context[self::ALREADY_CALLED]) && $data instanceof Institution;
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
