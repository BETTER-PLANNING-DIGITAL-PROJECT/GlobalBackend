<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Period;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PeriodMarkCheckExistingCalculatedAndValidatedMarksController extends AbstractController
{
    public function __construct(
        private readonly SchoolClassRepository                  $classRepository,
        private readonly StudentRegistrationRepository          $studentRegistrationRepository,
        private readonly EvaluationPeriodRepository             $evaluationPeriodRepository,
        private readonly MarkSequenceCourseCalculatedRepository $markSequenceCourseCalculatedRepository,
        private readonly MarkPeriodCourseCalculatedRepository $markPeriodCourseCalculatedRepository
    )
    {
    }

    const header = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'DNT, X-User-Token, Keep-Alive, User-Agent, X-Requested-With, If-Modified-Since, Cache-Control, Content-Type',
        'Access-Control-Max-Age' => 1728000,
        'Content-Type' => 'text/plain charset=UTF-8',
        'Content-Length' => 0
    ];

    public function checkValidatedMarks(array $students, EvaluationPeriod $evaluationPeriod): array
    {
        // On peut valider les notes pour les etudiants
        // s'il existe au moins 1 etudiant tel qu'il ait des notes calculees et non validees

        // On ne peut pas valider les notes pour les etudiants
        // si pour tout les etudiants toutes les notes sont validees
        $count = count($students);
        $notStudents = $count === 0;
        $notMarks = true;

        if (!$notStudents) {
            $i = 0;
            while ($i < $count && $notMarks) {
                $student = $students[$i];
                $notMarks = $this->markSequenceCourseCalculatedRepository->findOneBy(['student' => $student, 'evaluationPeriod' => $evaluationPeriod]) === null;
                $i++;
            }
        }

        $isPossibleToValidate = false;
        $isPossibleToUnvalidate = false;
        $notCalculatedMarks = true;

        if (!$notMarks) {
            $i = 0;
            while ($i < $count && (!$isPossibleToValidate || !$isPossibleToUnvalidate)) {
                $student = $students[$i];
                $markPeriodCourseCalculated = $this->markPeriodCourseCalculatedRepository->findOneBy(['student' => $student, 'evaluationPeriod' => $evaluationPeriod]);
                if ($markPeriodCourseCalculated) {
                    $notCalculatedMarks = false;
                    if ($markPeriodCourseCalculated->getIsValidated()) $isPossibleToUnvalidate = true;
                    else $isPossibleToValidate = true;
                }
                $i++;
            }
        }

        $result = ['notMarks' => $notMarks, 'isPossibleToValidate' => $isPossibleToValidate, 'isPossibleToUnvalidate' => $isPossibleToUnvalidate,'notCalculatedMarks' => $notCalculatedMarks];
        return $result; // Si on sort parce qu'il existe des notes non calculees , alors $i < count($checkCalculatedMarks)
    }

    // Verification d'une note calculee existante
    #[Route('api/school/mark/period/check-validated-marks/{data}', name: 'school_mark_period_check_validated_marks')]
    public function checkCalculatedMarksRoute(string $data): JsonResponse
    {
        $markData = json_decode($data, true);
        $students = null;
        if (isset($markData['studentIds'])) {
            $studentIds = $markData['studentIds'];
            $students = array_map(fn(int $studentId) => $this->studentRegistrationRepository->find($studentId), $studentIds);
        } else {
            $classId = $markData['classId'];
            $class = $this->classRepository->find($classId);
            $students = $this->studentRegistrationRepository->findBy(['currentClass' => $class]);
        }

        $evaluationPeriodId = $markData['evaluationPeriodId'];

        $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);
        $exists = $this->checkValidatedMarks($students, $evaluationPeriod);
        return $this->json($exists);
    }
}