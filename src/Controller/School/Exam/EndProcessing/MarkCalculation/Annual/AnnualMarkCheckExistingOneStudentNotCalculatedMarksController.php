<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Annual;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\GetConfigurationsUtil;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\ExamInstitutionSettings;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculated;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Configuration\ClassWeightingRepository;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\ExamInstitutionSettingsRepository;
use App\Repository\School\Exam\Configuration\FormulaThRepository;
use App\Repository\School\Exam\Configuration\MarkGradeRepository;
use App\Repository\School\Exam\Configuration\SchoolWeightingRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Configuration\SpecialityWeightingRepository;
use App\Repository\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\Security\Session\YearRepository;
use App\Repository\Setting\School\PeriodTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AnnualMarkCheckExistingOneStudentNotCalculatedMarksController extends AbstractController
{
    public function __construct(
        private readonly YearRepository                                $yearRepository,
        private readonly SchoolClassRepository                        $classRepository,
        private readonly StudentRegistrationRepository                $studentRegistrationRepository,
        private readonly PeriodTypeRepository                   $periodTypeRepository,
        private readonly EvaluationPeriodRepository                   $evaluationPeriodRepository,
        private readonly SequenceRepository                           $sequenceRepository,

        private readonly MarkGradeRepository                          $markGradeRepository,
        private readonly SchoolWeightingRepository                    $schoolWeightingRepository,
        private readonly ClassWeightingRepository                     $classWeightingRepository,
        private readonly SpecialityWeightingRepository                $specialityWeightingRepository,
        private readonly FormulaThRepository                          $formulaThRepository,
        private readonly ExamInstitutionSettingsRepository            $examInstitutionSettingsRepository,

        private readonly MarkPeriodCourseCalculatedRepository         $markPeriodCourseCalculatedRepository,
        private readonly MarkAnnualGeneralAverageCalculatedRepository $markAnnualGeneralAverageCalculatedRepository
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

    public function checkCalculatedMark(StudentRegistration $student, array $evaluationPeriods): array
    {
        $markAnnualGeneralAverageCalculated = $this->markAnnualGeneralAverageCalculatedRepository->findOneBy(['student' => $student]);
        $markPeriodCourseCalculateds = array_map(fn(EvaluationPeriod $evaluationPeriod)=>$this->markPeriodCourseCalculatedRepository->findOneBy(['student' => $student, 'evaluationPeriod' => $evaluationPeriod]),$evaluationPeriods);

        $markPeriodCourseExists = false;
        $i = 0;
        while ($i < count($markPeriodCourseCalculateds) && !$markPeriodCourseExists) {
            $markPeriodCourseCalculated = $markPeriodCourseCalculateds[$i];
            $markPeriodCourseExists = isset($markPeriodCourseCalculated);
        }

        return ['markAnnualGeneralAverageExists' => isset($markAnnualGeneralAverageCalculated), 'markPeriodCourseExists' => $markPeriodCourseExists];
    }

    public function checkCalculatedMarks(array $students, array $evaluationPeriods): array
    {
        // On peut calculer les notes pour les etudiants
        // s'il existe au moins 1 etudiant tel qu'il ait des notes existantes mais pas de notes calculees

        // On ne peut pas calculer les notes pour les etudiants
        // si pour tout les etudiants il y a des notes calculees
        $checkCalculatedMarks = array_map(fn(StudentRegistration $student) => $this->checkCalculatedMark($student, $evaluationPeriods), $students);
        $count = count($checkCalculatedMarks);
        $notStudents = $count === 0;
        $notMarks = true;
        $countEvaluationPeriods = count($evaluationPeriods);

        if (!$notStudents) {
            $i = 0;
            while ($i < $count && $notMarks) {
                $student = $students[$i];
                $j = 0;
                while ($j < $countEvaluationPeriods && $notMarks) {
                    $evaluationPeriod = $evaluationPeriods[$j];
                    $notMarks = $this->markPeriodCourseCalculatedRepository->findOneBy(['student' => $student, 'evaluationPeriod' => $evaluationPeriod]) === null;
                    $j++;
                }
                $i++;
            }
        }

        $isPossibleToCalculate = false;
        if (!$notMarks) {
            $i = 0;
            while ($i < $count) {
                $markPeriodCourseExists = $checkCalculatedMarks[$i]['markPeriodCourseExists'];
                $markAnnualGeneralAverageExists = $checkCalculatedMarks[$i]['markAnnualGeneralAverageExists'];
                if ($markPeriodCourseExists && !$markAnnualGeneralAverageExists) {
                    $isPossibleToCalculate = true;
                    break;
                }
                $i++;
            }
        }

        $result = ['notMarks' => $notMarks, 'isPossibleToCalculate' => $isPossibleToCalculate, 'notStudents' => $notStudents];
        return $result; // Si on sort parce qu'il existe des notes non calculees , alors $i < count($checkCalculatedMarks)
    }

    function getEvaluationPeriods(string $sequencePonderations): array
    {
        // 1:0:3!3.4-1:0:4!2.3
        $sequencePonderations = explode('-', $sequencePonderations); // ['1:0:3!3.4','1:0:4!2.3']
        $evaluationPeriods = [];
        foreach ($sequencePonderations as $sequencePonderation) {
            $sequencePonderationData = explode('!', $sequencePonderation); // ['1:0:3','3.4']
            $sequenceData = explode(':', $sequencePonderationData[0]); // ['1','0','3']
            $periodTypeWeightingId = $sequenceData[0]; // 1
            $periodType = $this->periodTypeRepository->find($periodTypeWeightingId); // 1
            $divisionNumberId = (int) $sequenceData[1]; // 0
            $evaluationPeriod = $this->evaluationPeriodRepository->findOneBy(['periodType'=>$periodType,'number'=> $divisionNumberId+1]);
            if ($evaluationPeriod) {
                $sequenceId = $sequenceData[2]; // 3
                $sequence = $this->sequenceRepository->find($sequenceId);
                $weighting = isset($sequencePonderationData[1]) && $sequencePonderationData[1] !== 'null' && $sequencePonderationData[1] !== '' ? floatval($sequencePonderationData[1]) : null;
                if (isset($weighting)) {
                    $evaluationPeriods[$evaluationPeriod->getId()][] = $sequence->getId();
                }
            }
        }
        return $evaluationPeriods;
    }

    // Verification d'une note calculee existante
    #[Route('api/school/mark/annual/check-calculated-marks/{data}', name: 'school_mark_annual_check_calculated_marks',)]
    public function checkCalculatedMarksRoute(string $data): JsonResponse
    {
        $markData = json_decode($data, true);
        $students = null;
        $classId = $markData['classId'];
        $class = $this->classRepository->find($classId);

        if (isset($markData['studentIds'])) {
            $studentIds = $markData['studentIds'];
            $students = array_map(fn(int $studentId) => $this->studentRegistrationRepository->find($studentId), $studentIds);
        } else {
            $students = $this->studentRegistrationRepository->findBy(['currentClass' => $class]);
        }

        $year = $this->yearRepository->find($markData['yearId']);

        // Recuperation des periodes d'evaluation a utiliser pour rechercher les notes calculees de periode
        $configurationsUtil = new GetConfigurationsUtil(
            $year,
            $this->formulaThRepository,
            $this->examInstitutionSettingsRepository,
            $this->schoolWeightingRepository,
            $this->classWeightingRepository,
            $this->specialityWeightingRepository,
            $this->markGradeRepository
        );

        $maxWeighting = $configurationsUtil->getMaxWeighting($class);
        if (!$maxWeighting) return $this->json('notMaxWeighting');

        // sequence ponderations

        // { 1:0:3!0.4-1:0:3!0.6 }
        $sequences = [];
        $evaluationPeriods = [];

        $sequencePonderations = $maxWeighting->getSequencePonderations();
        if (!$sequencePonderations) return $this->json('notSequencePonderations');

        // S'assurer que soit c'est vide soit c'est sur le bon format

        $evaluationPeriods = $this->getEvaluationPeriods($sequencePonderations);
        if(empty($evaluationPeriods)) return $this->json('notEvaluationPeriod');

//        $sequences = array_merge(...array_values($evaluationPeriods));
//        if(empty($sequences)) return 'notSequences';
        // Conversion en un array d EvaluationPeriod
        $evaluationPeriods = array_map(fn(int $evaluationPeriodId)=>$this->evaluationPeriodRepository->find($evaluationPeriodId),array_keys($evaluationPeriods));
        $exists = $this->checkCalculatedMarks($students,$evaluationPeriods);
        return $this->json($exists);
    }
}