<?php

namespace App\Controller\GetServices;

use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\Security\Session\YearRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class GetSequenceController extends AbstractController
{
    public function __construct(
        private readonly SequenceRepository $sequenceRepository,
        private readonly YearRepository $yearRepository,
        )
    {
    }

    #[Route('/api/get/sequence/year/{yearId}', name: 'get_sequence_by_year')]
    public function getSequenceByYear(string $yearId): JsonResponse
    {
        $year = $this->yearRepository->find($yearId);
        return $this->json($this->sequenceRepository->findBy(['year' => $year]));
    }
}
