<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Period;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\ExamInstitutionSettings;
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

class PeriodMarkCheckExistingOneStudentNotCalculatedMarksController extends AbstractController
{
    public function __construct(
        private readonly SchoolClassRepository                  $classRepository,
        private readonly StudentRegistrationRepository          $studentRegistrationRepository,
        private readonly EvaluationPeriodRepository             $evaluationPeriodRepository,
        private readonly MarkSequenceCourseCalculatedRepository                         $markSequenceCourseCalculatedRepository,
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

    public function checkCalculatedMark(StudentRegistration $student, EvaluationPeriod $evaluationPeriod): array
    {
        $markPeriodCourseCalculated = $this->markPeriodCourseCalculatedRepository->findOneBy(['student' => $student, 'evaluationPeriod' => $evaluationPeriod]);
        $markSequenceCourseExists = $this->markSequenceCourseCalculatedRepository->findOneBy(['student' => $student, 'evaluationPeriod' => $evaluationPeriod]);
        return ['markPeriodCourseExists' => isset($markPeriodCourseCalculated), 'markSequenceCourseExists' => isset($markSequenceCourseExists)];
    }

    public function checkCalculatedMarks(array $students, EvaluationPeriod $evaluationPeriod): array
    {
        // On peut calculer les notes pour les etudiants
        // s'il existe au moins 1 etudiant tel qu'il ait des notes existantes mais pas de notes calculees

        // On ne peut pas calculer les notes pour les etudiants
        // si pour tout les etudiants il y a des notes calculees
        $checkCalculatedMarks = array_map(fn(StudentRegistration $student) => $this->checkCalculatedMark($student, $evaluationPeriod), $students);
        $count = count($checkCalculatedMarks);
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

        $isPossibleToCalculate = false;
        if (!$notMarks) {
            $i = 0;
            while ($i < $count) {
                $markSequenceCourseExists = $checkCalculatedMarks[$i]['markSequenceCourseExists'];
                $markPeriodCourseExists = $checkCalculatedMarks[$i]['markPeriodCourseExists'];
                if ($markSequenceCourseExists && !$markPeriodCourseExists) {
                    $isPossibleToCalculate = true;
                    break;
                }
                $i++;
            }
        }

        $result = ['notMarks' => $notMarks, 'isPossibleToCalculate' => $isPossibleToCalculate, 'notStudents' => $notStudents];
        return $result; // Si on sort parce qu'il existe des notes non calculees , alors $i < count($checkCalculatedMarks)
    }

    // Verification d'une note calculee existante
    #[Route('api/school/mark/period/check-calculated-marks/{data}', name: 'school_mark_period_check_calculated_marks',)]
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
        $exists = $this->checkCalculatedMarks($students, $evaluationPeriod);
        return $this->json($exists);
    }
}