<?php

namespace App\Controller\School\Exam\EndProcessing\MarkValidation\Annual;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\GetConfigurationsUtil;
use App\Controller\School\Exam\EndProcessing\MarkValidation\Services\Annual\AnnualMarkValidationValidateDeleteUtil;
use App\Controller\School\Exam\EndProcessing\MarkValidation\Services\Annual\GetServices\AnnualMarkPrimaryAndSecondaryValidationGetAllMarksCalculatedUtil;
use App\Controller\School\Exam\EndProcessing\MarkValidation\Services\Annual\GetServices\AnnualMarkTernaryValidationGetAllMarksCalculatedUtil;
use App\Repository\School\Exam\Configuration\ClassWeightingRepository;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\ExamInstitutionSettingsRepository;
use App\Repository\School\Exam\Configuration\FormulaThRepository;
use App\Repository\School\Exam\Configuration\MarkGradeRepository;
use App\Repository\School\Exam\Configuration\SchoolWeightingRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Configuration\SpecialityWeightingRepository;
use App\Repository\School\Exam\Operation\Annual\Course\MarkAnnualCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Annual\Course\MarkAnnualCoursePeriodCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Annual\Course\MarkAnnualCourseSequenceCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCourseTernaryCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAveragePeriodCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageSequenceCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Annual\Module\MarkAnnualModuleCalculatedRepository;
use App\Repository\School\Exam\Operation\Annual\Module\MarkAnnualModuleCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculatedRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\Security\Session\YearRepository;
use App\Repository\Setting\School\PeriodTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AnnualMarkValidationController extends AbstractController
{

    public function __construct(
        private readonly YearRepository                           $yearRepository,
        private readonly SchoolClassRepository                           $classRepository,
        private readonly StudentRegistrationRepository                           $studentRegistrationRepository,
        private readonly PeriodTypeRepository $periodTypeRepository,
        private readonly EvaluationPeriodRepository $evaluationPeriodRepository,
        private readonly ClassProgramRepository $classProgramRepository,
        private readonly StudentCourseRegistrationRepository $studentCourseRegistrationRepository,
        private readonly SequenceRepository                              $sequenceRepository,

        private readonly MarkGradeRepository $markGradeRepository,

        private readonly SchoolWeightingRepository                                     $schoolWeightingRepository,
        private readonly ClassWeightingRepository                                      $classWeightingRepository,
        private readonly SpecialityWeightingRepository                                 $specialityWeightingRepository,
        private readonly FormulaThRepository                                           $formulaThRepository,
        private readonly ExamInstitutionSettingsRepository                             $examInstitutionSettingsRepository,


        private readonly MarkSequenceCourseCalculatedRepository                        $markSequenceCourseCalculatedRepository,
        private readonly MarkSequenceModuleCalculatedRepository                        $markSequenceModuleCalculatedRepository,
        private readonly MarkSequenceGeneralAverageCalculatedRepository                $markSequenceGeneralAverageCalculatedRepository,

        private readonly MarkPeriodCourseCalculatedRepository                          $markPeriodCourseCalculatedRepository,
        private readonly MarkPeriodModuleCalculatedRepository                                           $markPeriodModuleCalculatedRepository,
        private readonly MarkPeriodModuleCalculationRelationRepository                                  $markPeriodModuleCalculationRelationRepository,
        private readonly MarkPeriodGeneralAverageCalculatedRepository                                   $markPeriodGeneralAverageCalculatedRepository,

        private readonly MarkAnnualCourseCalculatedRepository                                           $markAnnualCourseCalculatedRepository,
        private readonly MarkAnnualCourseSequenceCalculationRelationRepository                          $markAnnualCourseSequenceCalculationRelationRepository,
        private readonly MarkAnnualCoursePeriodCalculationRelationRepository                            $markAnnualCoursePeriodCalculationRelationRepository,
        private readonly MarkAnnualModuleCalculatedRepository                                           $markAnnualModuleCalculatedRepository,
        private readonly MarkAnnualModuleCalculationRelationRepository                                  $markAnnualModuleCalculationRelationRepository,
        private readonly MarkAnnualGeneralAverageCalculatedRepository                                   $markAnnualGeneralAverageCalculatedRepository,
        private readonly MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelationRepository $markAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelationRepository,
        private readonly MarkAnnualGeneralAverageCourseTernaryCalculationRelationRepository $markAnnualGeneralAverageCourseTernaryCalculationRelationRepository,
        private readonly MarkAnnualGeneralAverageSequenceCalculationRelationRepository                  $markAnnualGeneralAverageSequenceCalculationRelationRepository,
        private readonly MarkAnnualGeneralAveragePeriodCalculationRelationRepository                    $markAnnualGeneralAveragePeriodCalculationRelationRepository,

        private readonly EntityManagerInterface                                                         $entityManager
    )
    {

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
                    $evaluationPeriods[$evaluationPeriod->getId()][] = $sequence;
                }
            }
        }
        return $evaluationPeriods;
    }


    // Afficher les notes annuelles d'un etudiant
    #[Route('api/school/mark/annual/get-all-marks/{schoolSystem}/{data}', name: 'school_mark_annual_get_all_marks')]
    public function getAllMarks(string $data,string $schoolSystem): JsonResponse
    {
        $markData = json_decode($data, true);

        $studentId = $markData['studentId'];

        $student = $this->studentRegistrationRepository->find($studentId);

        $yearId = $markData['yearId'];
        $classId = $markData['classId'];
        $class = $this->classRepository->find($classId);

        $year = $this->yearRepository->find($yearId);
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

        $class = null;
        switch ($schoolSystem){
            case 'primary':
                $class = AnnualMarkPrimaryAndSecondaryValidationGetAllMarksCalculatedUtil::class;
                break;

            case 'ternary':
                $class = AnnualMarkTernaryValidationGetAllMarksCalculatedUtil::class;
                break;
        }
        $annualMarkValidationGetAllMarksCalculatedUtil = new $class($student,$evaluationPeriods,$this->evaluationPeriodRepository,$this->studentCourseRegistrationRepository,$this->markSequenceCourseCalculatedRepository,$this->markSequenceModuleCalculatedRepository,$this->markSequenceGeneralAverageCalculatedRepository,$this->markPeriodCourseCalculatedRepository, $this->markPeriodModuleCalculatedRepository, $this->markPeriodModuleCalculationRelationRepository, $this->markPeriodGeneralAverageCalculatedRepository,
            $this->markAnnualCourseCalculatedRepository,
            $this->markAnnualModuleCalculatedRepository,
            $this->markAnnualModuleCalculationRelationRepository,
            $this->markAnnualGeneralAverageCalculatedRepository);
        $allMarksCalculatedDatas = $annualMarkValidationGetAllMarksCalculatedUtil->getAllMarksCalculated($student);

        return $this->json($allMarksCalculatedDatas);
    }

    // Valider ou supprimer les notes
    #[Route('api/school/mark/annual/validateOrDelete/{schoolSystem}/{data}', name: 'school_mark_annual_validate_or_delete')]
    public function validateOrDelete(string $data,string $schoolSystem): JsonResponse
    {
        $markData = json_decode($data, true);

        $validate = $markData['validate'];

        $classId = $markData['classId'];
        $class = $this->classRepository->find($classId);
        $students = null;
        if (isset($markData['studentIds'])) {
            $studentIds = $markData['studentIds'];
            $students = array_map(fn(int $studentId) => $this->studentRegistrationRepository->find($studentId), $studentIds);
        }
        else{
            $students = $this->studentRegistrationRepository->findBy(['currentClass' => $class]);
        }

        $institution = $class->getInstitution();
        $examInstitutionSettings = $this->examInstitutionSettingsRepository->findOneBy(['institution' => $institution]);
        if (!$examInstitutionSettings) return $this->json('notExamInstitutionSettings');

        $dontClassifyTheExcluded = $examInstitutionSettings->getDontClassifyTheExcluded();
        $calculateSubjectsRanks = $examInstitutionSettings->getCalculateSubjectsRanks();

        $markAnnualGeneralAverageCourseCalculationRelationRepository =
            $this->{'markAnnualGeneralAverageCourse'.($schoolSystem === 'ternary' ? 'Ternary':'PrimaryAndSecondary').'CalculationRelationRepository'};


        $annualMarkValidationValidateDeleteUtil = new AnnualMarkValidationValidateDeleteUtil(
            $calculateSubjectsRanks,
            $dontClassifyTheExcluded,
            $validate,
            $schoolSystem,
            $this->classProgramRepository,
            $this->markAnnualCourseCalculatedRepository,
            $this->markAnnualCourseSequenceCalculationRelationRepository,
            $this->markAnnualCoursePeriodCalculationRelationRepository,
            $this->markAnnualModuleCalculatedRepository,
            $this->markAnnualModuleCalculationRelationRepository,
            $this->markAnnualGeneralAverageCalculatedRepository,
            $this->markAnnualGeneralAverageSequenceCalculationRelationRepository,
            $markAnnualGeneralAverageCourseCalculationRelationRepository,
            $this->markAnnualGeneralAveragePeriodCalculationRelationRepository,
            $this->entityManager
        );
        $result = $annualMarkValidationValidateDeleteUtil->validateOrDeleteMarks('Students', $students);

        return $this->json($result);
    }
}