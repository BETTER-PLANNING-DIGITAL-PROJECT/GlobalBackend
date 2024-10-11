<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Period;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Generation\Student\Course\PeriodMarkGenerationCourseUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\ClassificationUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\GetConfigurationsUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Calculation\Student\ClassificationPeriodUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Calculation\Student\GeneralAverage\PeriodMarkCalculationGeneralAverageUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Calculation\Student\Module\PeriodMarkCalculationModulesUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Calculation\Student\Module\PeriodMarkCalculationModuleUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Calculation\Student\WorkRemarksPeriodUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Generation\Student\GeneralAverage\PeriodMarkGenerationGeneralAverageUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Calculation\Student\Course\PeriodMarkCalculationCoursesUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Calculation\Student\Course\PeriodMarkCalculationCourseUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Calculation\Student\PeriodMarkCalculationAllUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Generation\Student\Module\PeriodMarkGenerationModuleUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Generation\Student\PeriodMarkGenerationUtil;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\ClassWeightingRepository;
use App\Repository\School\Exam\Configuration\ExamInstitutionSettingsRepository;
use App\Repository\School\Exam\Configuration\FormulaThRepository;
use App\Repository\School\Exam\Configuration\MarkGradeRepository;
use App\Repository\School\Exam\Configuration\SchoolWeightingRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Configuration\SpecialityWeightingRepository;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculatedRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Configuration\ModuleRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class PeriodMarkCalculationControllerUtil extends AbstractController
{

    public function __construct(
        private readonly ModuleRepository                               $moduleRepository,
        private readonly ClassProgramRepository                         $classProgramRepository,
        private readonly SequenceRepository                             $sequenceRepository,

        private readonly StudentCourseRegistrationRepository            $studentCourseRegistrationRepository,

        private readonly MarkGradeRepository                            $markGradeRepository,

        private readonly SchoolWeightingRepository                      $schoolWeightingRepository,
        private readonly ClassWeightingRepository                       $classWeightingRepository,
        private readonly SpecialityWeightingRepository                  $specialityWeightingRepository,
        private readonly FormulaThRepository                            $formulaThRepository,
        private readonly ExamInstitutionSettingsRepository              $examInstitutionSettingsRepository,

        private readonly MarkSequenceCourseCalculatedRepository         $markSequenceCourseCalculatedRepository,
        private readonly MarkSequenceGeneralAverageCalculatedRepository $markSequenceGeneralAverageCalculatedRepository,
        private readonly MarkPeriodCourseCalculatedRepository           $markPeriodCourseCalculatedRepository,
        private readonly MarkPeriodGeneralAverageCalculatedRepository   $markPeriodGeneralAverageCalculatedRepository,

        private readonly EntityManagerInterface                         $entityManager
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

    function getSequenceWeightings(string $sequencePonderations, int $periodTypeId, int $divisionNumber, string $halfYearAverageFormula): array
    {
        // 1:0:3!3.4-1:0:4!2.3
        $sequencePonderations = explode('-', $sequencePonderations); // ['1:0:3!3.4','1:0:4!2.3']
        $sequenceWeightings = [];
        $sequenceWeightings['sequences'] = [];
        if ($halfYearAverageFormula === '1') {
            foreach ($sequencePonderations as $sequencePonderation) {
                $sequencePonderationData = explode('!', $sequencePonderation); // ['1:0:3','3.4']
                $sequenceData = explode(':', $sequencePonderationData[0]); // ['1','0','3']
                $periodTypeWeightingId = $sequenceData[0]; // 1
                $divisionNumberId = $sequenceData[1]; // 0
                if ($periodTypeWeightingId == $periodTypeId && $divisionNumberId == $divisionNumber) {
                    $sequenceId = $sequenceData[2]; // 3
                    $weighting = isset($sequencePonderationData[1]) && $sequencePonderationData[1] !== 'null' && $sequencePonderationData[1] !== '' ? floatval($sequencePonderationData[1]) : null;
                    if (isset($weighting)) {
                        $sequence = $this->sequenceRepository->find($sequenceId);
                        $sequenceWeightings['sequences'][] = $sequence;
                        $sequenceWeightings['weightings'][$sequenceId] = $weighting;
                    }
                }
            }
        } else {
            foreach ($sequencePonderations as $sequencePonderation) {
                $sequencePonderationData = explode('!', $sequencePonderation); // ['1:0:3','3.4']
                $sequenceData = explode(':', $sequencePonderationData[0]); // ['1','0','3']
                $periodTypeWeightingId = $sequenceData[0]; // 1
                $divisionNumberId = $sequenceData[1]; // 0
                if ($periodTypeWeightingId == $periodTypeId && $divisionNumberId == $divisionNumber) {
                    $sequenceId = $sequenceData[2]; // 3
                    $weighting = isset($sequencePonderationData[1]) && $sequencePonderationData[1] !== 'null' && $sequencePonderationData[1] !== '' ? floatval($sequencePonderationData[1]) : null;
                    if (isset($weighting)) {
                        $sequence = $this->sequenceRepository->find($sequenceId);
                        $sequenceWeightings['sequences'][] = $sequence;
                    }
                }
            }
        }
        return $sequenceWeightings;
    }

    public function calculateAllMarks(
        Institution      $institution,
        Year             $year,
        School           $school,
        SchoolClass      $class,
        EvaluationPeriod $evaluationPeriod,
        User             $user,
        string           $case, mixed $element)
    {
        $configurationsUtil = new GetConfigurationsUtil(
            $year,
            $this->formulaThRepository,
            $this->examInstitutionSettingsRepository,
            $this->schoolWeightingRepository,
            $this->classWeightingRepository,
            $this->specialityWeightingRepository,
            $this->markGradeRepository
        );

        $formulaTh = $configurationsUtil->getFormulaTh();

        if (!$formulaTh) return 'notFormulaTh';

        $examInstitutionSettings = $configurationsUtil->getExamInstitutionSettings();

        if (!$examInstitutionSettings) return 'notExamInstitutionSettings';

        $maxWeighting = $configurationsUtil->getMaxWeighting($class);
        if (!$maxWeighting) return 'notMaxWeighting';

        // sequence ponderations
        $periodType = $maxWeighting->getPeriodType();
        $numberOfDivision = $maxWeighting->getNumberOfDivision();
        $number = $evaluationPeriod->getNumber();
        if ($periodType !== $evaluationPeriod->getPeriodType()) {
            return 'differentPeriodType';
        } else if ($numberOfDivision < $number) return 'differentNumberOfDivision';

        $halfYearAverageFormula = $formulaTh->getHalfYearAverageFormula();

        // { 1:0:3!0.4-1:0:3!0.6 }
        $sequences = [];
        $weightings = [];

        $sequencePonderations = $maxWeighting->getSequencePonderations();
        if (!$sequencePonderations) return 'notSequencePonderations';

        // S'assurer que soit c'est vide soit c'est sur le bon format

        $sequenceWeightings = $this->getSequenceWeightings($sequencePonderations, $periodType->getId(), $number - 1, $halfYearAverageFormula);
        $sequenceDatas = $sequenceWeightings;
        $sequences = (array) $sequenceDatas['sequences'];
        if (empty($sequences)) return 'notSequences';

        $weightings = (isset($sequenceDatas['weightings'])) ? $sequenceDatas['weightings'] : [];

        // Configurations
        $assign0WhenTheMarkIsNotEntered = $examInstitutionSettings->getAssign0WhenTheMarkIsNotEntered();
        $isCoefficientOfNullMarkConsiderInTheAverageCalculation = $maxWeighting->isIsCoefficientOfNullMarkConsiderInTheAverageCalculation();
        $calculateAveragesForUnclassified = $examInstitutionSettings->getCalculateAveragesForUnclassified();
        $activateEliminationMarks = $examInstitutionSettings->getActivateEliminationMarks();
        $dontClassifyTheExcluded = $examInstitutionSettings->getDontClassifyTheExcluded();
        $calculateSubjectsRanks = $examInstitutionSettings->getCalculateSubjectsRanks();

        $percentageSubjectNumber = $formulaTh->getPercentageSubjectNumber();
        $andOr = $formulaTh->getAndOr();
        $percentageTotalCoefficient = $formulaTh->getPercentageTotalCoefficient();

        $blameWorkAverage = $formulaTh->getBlameWorkAverage();
        $warningWorkAverage = $formulaTh->getWarningWorkAverage();

        $grantEncouragementAverage = $formulaTh->getGrantEncouragementAverage();
        $grantCongratulationAverage = $formulaTh->getGrantCongratulationAverage();
        $grantThCompositionAverage = $formulaTh->getGrantThCompositionAverage();

        // Configurations de la periode
        $isMarkForAllSequenceRequired = $maxWeighting->isIsMarkForAllSequenceRequired();
        $validationBase = $maxWeighting->getValidationMark();
        $isValidateCompensateModulate = $maxWeighting->isIsValidateCompensateModulate();
        $groupEliminateAverage = $maxWeighting->getGroupEliminateAverage();
        $generalEliminateAverage = $maxWeighting->getGeneralEliminateAverage();

        $periodMarkGenerationUtil = new PeriodMarkGenerationUtil($configurationsUtil, $institution, $year, $school, $class, $evaluationPeriod, $user, $this->entityManager);

        // Initialisation des services pour les matieres
        $periodMarkGenerationCourseUtil = new PeriodMarkGenerationCourseUtil($this->entityManager, $periodMarkGenerationUtil);
        $periodMarkCalculationCourseUtil = new PeriodMarkCalculationCourseUtil($halfYearAverageFormula, $assign0WhenTheMarkIsNotEntered, $isMarkForAllSequenceRequired, $evaluationPeriod, $sequences, $weightings, $this->studentCourseRegistrationRepository, $this->markSequenceCourseCalculatedRepository);
        $periodMarkCalculationCoursesUtil = new PeriodMarkCalculationCoursesUtil($halfYearAverageFormula, $validationBase, $evaluationPeriod, $sequences, $periodMarkGenerationCourseUtil, $periodMarkCalculationCourseUtil, $this->markSequenceGeneralAverageCalculatedRepository);

        // Initialisation des services pour les modules
        $periodMarkGenerationModuleUtil = new PeriodMarkGenerationModuleUtil($this->entityManager, $periodMarkGenerationUtil);
        $periodMarkCalculationModuleUtil = new PeriodMarkCalculationModuleUtil(
            $assign0WhenTheMarkIsNotEntered,
            $isCoefficientOfNullMarkConsiderInTheAverageCalculation,
            $isValidateCompensateModulate,
            $validationBase,
            $activateEliminationMarks,
            $groupEliminateAverage,
            $this->moduleRepository
        );
        $periodMarkCalculationModulesUtil = new PeriodMarkCalculationModulesUtil($validationBase, $groupEliminateAverage, $periodMarkGenerationModuleUtil, $periodMarkCalculationModuleUtil);
        $isEliminationModuleActivated = $periodMarkCalculationModuleUtil->isEliminable();

        // Initialisation des services pour la moyenne generale
        $periodMarkGenerationGeneralAverageUtil = new PeriodMarkGenerationGeneralAverageUtil($halfYearAverageFormula, $evaluationPeriod, $this->markPeriodCourseCalculatedRepository, $this->entityManager, $periodMarkGenerationUtil);
        $classificationUtil = new ClassificationPeriodUtil($calculateAveragesForUnclassified, $percentageSubjectNumber, $andOr, $percentageTotalCoefficient, $sequences, $this->markSequenceCourseCalculatedRepository);
        $workRemarksPeriodUtil = new WorkRemarksPeriodUtil($blameWorkAverage, $warningWorkAverage, $grantEncouragementAverage, $grantCongratulationAverage, $grantThCompositionAverage);
        $periodMarkCalculationGeneralAverageUtil = new PeriodMarkCalculationGeneralAverageUtil($assign0WhenTheMarkIsNotEntered, $isCoefficientOfNullMarkConsiderInTheAverageCalculation,$calculateSubjectsRanks, $generalEliminateAverage, $activateEliminationMarks, $isEliminationModuleActivated, $halfYearAverageFormula,$dontClassifyTheExcluded, $class, $evaluationPeriod, $periodMarkGenerationGeneralAverageUtil, $classificationUtil, $workRemarksPeriodUtil, $this->classProgramRepository, $this->markPeriodCourseCalculatedRepository, $this->markPeriodGeneralAverageCalculatedRepository, $this->entityManager);
        $periodMarkCalculationAllUtil = new PeriodMarkCalculationAllUtil($evaluationPeriod, $this->markPeriodCourseCalculatedRepository, $periodMarkCalculationCoursesUtil, $periodMarkCalculationModulesUtil, $periodMarkCalculationGeneralAverageUtil, $this->entityManager);

        $periodMarkCalculationAllUtil->calculatePeriodAllMarks($case, $element);

        return 'success';
//        return $this->json('success',200,self::header);
    }
}