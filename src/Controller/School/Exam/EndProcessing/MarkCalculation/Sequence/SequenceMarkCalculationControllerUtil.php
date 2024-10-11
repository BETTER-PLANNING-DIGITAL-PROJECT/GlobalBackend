<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Sequence;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\ClassificationUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\GetConfigurationsUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\ClassificationSequenceUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\Course\SequenceMarkCalculationCoursesUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\Course\SequenceMarkCalculationCourseUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\GeneralAverage\SequenceMarkCalculationGeneralAverageUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\Module\SequenceMarkCalculationModulesUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\Module\SequenceMarkCalculationModuleUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\SequenceMarkCalculationAllUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\WorkRemarksSequenceUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Generation\Student\Course\SequenceMarkGenerationCourseUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Generation\Student\GeneralAverage\SequenceMarkGenerationGeneralAverageUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Generation\Student\Module\SequenceMarkGenerationModuleUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Generation\Student\SequenceMarkGenerationUtil;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculated;
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
use App\Repository\School\Exam\Configuration\SpecialityWeightingRepository;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculatedRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Configuration\ModuleRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class SequenceMarkCalculationControllerUtil extends AbstractController
{

    public function __construct(
        private readonly ModuleRepository $moduleRepository,
        
        private readonly ClassProgramRepository $classProgramRepository,
        private readonly StudentCourseRegistrationRepository $studentCourseRegistrationRepository,
        private readonly MarkRepository $markRepository,
        
        private readonly MarkGradeRepository $markGradeRepository,
        
        private readonly SchoolWeightingRepository            $schoolWeightingRepository,
        private readonly ClassWeightingRepository             $classWeightingRepository,
        private readonly SpecialityWeightingRepository        $specialityWeightingRepository,
        private readonly FormulaThRepository                  $formulaThRepository,
        private readonly ExamInstitutionSettingsRepository    $examInstitutionSettingsRepository,

        private readonly MarkSequenceCourseCalculatedRepository    $markSequenceCourseCalculatedRepository,
        private readonly MarkSequenceGeneralAverageCalculatedRepository    $markSequenceGeneralAverageCalculatedRepository,

        private readonly EntityManagerInterface $entityManager
    ){}

    public function calculateAllMarks(
        Institution $institution,
        Year $year,
        School $school,
        SchoolClass $class,
        EvaluationPeriod $evaluationPeriod,
        Sequence $sequence,
        User $user,
        string $case,mixed $element)
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

        $sequenceMarkGenerationUtil = new SequenceMarkGenerationUtil($configurationsUtil, $institution, $year, $school, $class, $evaluationPeriod, $sequence, $user, $this->entityManager);

        // Initialisation des services pour les matieres
        $sequenceMarkGenerationCourseUtil = new SequenceMarkGenerationCourseUtil($this->entityManager,$sequenceMarkGenerationUtil);
        $sequenceMarkCalculationCourseUtil = new SequenceMarkCalculationCourseUtil($assign0WhenTheMarkIsNotEntered,$evaluationPeriod,$sequence,$this->studentCourseRegistrationRepository,$this->markRepository);
        $sequenceMarkCalculationCoursesUtil = new SequenceMarkCalculationCoursesUtil($sequenceMarkGenerationCourseUtil,$sequenceMarkCalculationCourseUtil);

        // Initialisation des services pour les modules
        $sequenceMarkGenerationModuleUtil = new SequenceMarkGenerationModuleUtil($this->entityManager,$sequenceMarkGenerationUtil);
        $sequenceMarkCalculationModuleUtil = new SequenceMarkCalculationModuleUtil(
            $assign0WhenTheMarkIsNotEntered,
            $isCoefficientOfNullMarkConsiderInTheAverageCalculation,
            $this->moduleRepository
        );
        $sequenceMarkCalculationModulesUtil = new SequenceMarkCalculationModulesUtil($sequenceMarkGenerationModuleUtil, $sequenceMarkCalculationModuleUtil);

        // Initialisation des services pour la moyenne generale
        $sequenceMarkGenerationGeneralAverageUtil = new SequenceMarkGenerationGeneralAverageUtil($this->entityManager,$sequenceMarkGenerationUtil);
        $classificationUtil = new ClassificationSequenceUtil($calculateAveragesForUnclassified,$percentageSubjectNumber, $andOr, $percentageTotalCoefficient,$sequence,$this->markRepository);
        $workRemarksSequenceUtil = new WorkRemarksSequenceUtil($blameWorkAverage, $warningWorkAverage, $grantEncouragementAverage, $grantCongratulationAverage);
        $sequenceMarkCalculationGeneralAverageUtil = new SequenceMarkCalculationGeneralAverageUtil($assign0WhenTheMarkIsNotEntered,$isCoefficientOfNullMarkConsiderInTheAverageCalculation,$calculateSubjectsRanks,$activateEliminationMarks,$dontClassifyTheExcluded,$class,$evaluationPeriod,$sequence,$sequenceMarkGenerationGeneralAverageUtil, $classificationUtil,$workRemarksSequenceUtil,$this->classProgramRepository,$this->markSequenceGeneralAverageCalculatedRepository,$this->markSequenceCourseCalculatedRepository,$this->entityManager);
        $sequenceMarkCalculationAllUtil = new SequenceMarkCalculationAllUtil($evaluationPeriod,$sequence,$this->markSequenceCourseCalculatedRepository,$sequenceMarkCalculationCoursesUtil, $sequenceMarkCalculationModulesUtil, $sequenceMarkCalculationGeneralAverageUtil,$this->entityManager);

        $sequenceMarkCalculationAllUtil->calculateSequenceAllMarks($case,$element);

        return 'success';
    }
}