<?php

namespace App\Controller\GetServices;

use App\Repository\School\Schooling\Configuration\LevelRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class GetLevelController extends AbstractController
{
    public function __construct(
        private readonly LevelRepository       $levelRepository,
        private readonly SchoolRepository      $schoolRepository,
        private readonly SchoolClassRepository $classRepository
    )
    {
    }

    public function __invoke(Request $request): array
    {
        $schoolId = $request->attributes->get('schoolId');

        $school = $this->schoolRepository->find($schoolId);

        $levelIds = $this->classRepository->findLevelsBySchool($school);
        $levels = array_map(fn(int $levelId) => $this->levelRepository->find($levelId), array_column($levelIds, 'levelId'));
        return $levels;
    }

    #[Route('/api/get/level/by/school/promotion/{schoolId}', name: 'get_promotion_levels')]
    public function getPromotionLevelBySchool(string $schoolId): JsonResponse
    {
        $school = $this->schoolRepository->find($schoolId);
        $levelIds = $this->classRepository->findPromotionLevels($school, true);
        $levels = array_map(fn(int $levelId) => $this->levelRepository->find($levelId), array_column($levelIds, 'levelId'));
        return $this->json($levels);
    }

    #[Route('/api/get/level/by/school/graduation/{schoolId}', name: 'get_graduation_levels')]
    public function getGraduationLevelBySchool(string $schoolId): JsonResponse
    {
        $school = $this->schoolRepository->find($schoolId);
        $levelIds = $this->classRepository->findPromotionLevels($school, false);
        $levels = array_map(fn(int $levelId) => $this->levelRepository->find($levelId), array_column($levelIds, 'levelId'));
        return $this->json($levels);
    }
}
