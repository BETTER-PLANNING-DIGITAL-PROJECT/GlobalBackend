<?php

namespace App\Controller\GetServices;

use App\Repository\School\Exam\Configuration\FormulaConditionRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Schooling\Configuration\LevelRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\Security\Session\YearRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class GetFormulaConditionController extends AbstractController
{
    public function __construct(
        private readonly LevelRepository       $levelRepository,
        private readonly SchoolRepository      $schoolRepository,
        private readonly FormulaConditionRepository $formulaConditionRepository
    )
    {
    }

    public function __invoke(Request $request): array
    {
        $schoolId = $request->attributes->get('schoolId');
        $levelId = $request->attributes->get('levelId');

        $school = $this->schoolRepository->find($schoolId);
        $level = $this->levelRepository->find($levelId);

        $formulaConditions = $this->formulaConditionRepository->getPreviousLevelsFormula($school, $level);
        return $formulaConditions;
    }
}
