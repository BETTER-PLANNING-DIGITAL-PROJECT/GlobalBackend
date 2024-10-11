<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Annual;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\All\AnnualMarkCalculationAllPrimaryAndSecondaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\All\AnnualMarkCalculationAllTernaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Classification\ClassificationAnnualPrimaryAndSecondaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Classification\ClassificationAnnualTernaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Course\PrimaryAndSecondary\AnnualMarkCalculationCoursePrimaryAndSecondaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Course\PrimaryAndSecondary\AnnualMarkCalculationCoursesPrimaryAndSecondaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Course\Ternary\AnnualMarkCalculationCoursesTernaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Course\Ternary\AnnualMarkCalculationCourseTernaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\GeneralAverage\AnnualMarkCalculationGeneralAveragePrimaryAndSecondaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\GeneralAverage\AnnualMarkCalculationGeneralAverageTernaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Module\PrimaryAndSecondary\AnnualMarkCalculationModulePrimaryAndSecondaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Module\PrimaryAndSecondary\AnnualMarkCalculationModulesPrimaryAndSecondaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\WorkRemarksAnnualUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Generation\Student\AnnualMarkGenerationUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Generation\Student\Course\AnnualMarkGenerationCourseUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Generation\Student\GeneralAverage\AnnualMarkGenerationGeneralAverageUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Generation\Student\Module\AnnualMarkGenerationModuleUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\GetConfigurationsUtil;
use App\Entity\School\Exam\Configuration\SpecialityWeighting;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\ClassWeightingRepository;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\ExamInstitutionSettingsRepository;
use App\Repository\School\Exam\Configuration\FormulaThRepository;
use App\Repository\School\Exam\Configuration\MarkGradeRepository;
use App\Repository\School\Exam\Configuration\SchoolWeightingRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Configuration\SpecialityWeightingRepository;
use App\Repository\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Annual\Course\MarkAnnualCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculatedRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Study\Configuration\ModuleRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\Setting\School\PeriodTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AnnualMarkCalculationControllerUtil extends AbstractController
{

    public function __construct(
        private readonly PeriodTypeRepository $periodTypeRepository,
        private readonly ClassProgramRepository $classProgramRepository,
        private readonly ModuleRepository $moduleRepository,
        private readonly EvaluationPeriodRepository $evaluationPeriodRepository,
        private readonly SequenceRepository $sequenceRepository,

        private readonly StudentCourseRegistrationRepository $studentCourseRegistrationRepository,

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

        private readonly MarkAnnualCourseCalculatedRepository   $markAnnualCourseCalculatedRepository,
        private readonly MarkAnnualGeneralAverageCalculatedRepository   $markAnnualGeneralAverageCalculatedRepository,

        private readonly EntityManagerInterface                         $entityManager
    ){}

    const header = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'DNT, X-User-Token, Keep-Alive, User-Agent, X-Requested-With, If-Modified-Since, Cache-Control, Content-Type',
        'Access-Control-Max-Age' => 1728000,
        'Content-Type' => 'text/plain charset=UTF-8',
        'Content-Length' => 0
    ];

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

    public function calculateAllMarks(
        Institution $institution,
        Year $year,
        School $school,
        SchoolClass $class,
        User $user,
        string $case,mixed $element,string $schoolSystem)
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

        $finalAverageFormula = $formulaTh->getFinalAverageFormula();
        $finalMarkFormula = $formulaTh->getFinalMarkFormula();

        // { 1:0:3!0.4-1:0:3!0.6 }
        $sequences = [];
        $evaluationPeriods = [];

        $sequencePonderations = $maxWeighting->getSequencePonderations();
        if (!$sequencePonderations) return 'notSequencePonderations';

        // S'assurer que soit c'est vide soit c'est sur le bon format
        $evaluationPeriods = [];

//        if ($finalAverageFormula){
            $evaluationPeriods = $this->getEvaluationPeriods($sequencePonderations);
            if (empty($evaluationPeriods)) return 'notEvaluationPeriod';

/*            $sequences = array_merge(...array_values($evaluationPeriods));
            if (empty($sequences)) return 'notSequences';*/
//        }

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
        $grantThAnnualAverage = $formulaTh->getGrantThAnnualAverage();

        // Configurations de la periode
        $isMarkForAllSequenceRequired = $maxWeighting->isIsMarkForAllSequenceRequired();
        $validationBase = $maxWeighting->getValidationMark();
        $isValidateCompensateModulate = $maxWeighting->isIsValidateCompensateModulate();
        $groupEliminateAverage = $maxWeighting->getGroupEliminateAverage();
        $generalEliminateAverage = $maxWeighting->getGeneralEliminateAverage();

        $annualMarkGenerationUtil = new AnnualMarkGenerationUtil($configurationsUtil, $institution, $year, $school, $class, $user, $this->entityManager);

        $evaluationPeriodsArray = $evaluationPeriods;
        $evaluationPeriods = array_map(fn(int $evaluationPeriodId)=>$this->evaluationPeriodRepository->find($evaluationPeriodId),array_keys($evaluationPeriods));

        // Initialisation des services en fonction des etapes du systeme educatif
        $annualMarkCalculationCourseUtil = $annualMarkCalculationCoursesUtil = null;
        $annualMarkCalculationModuleUtil = $annualMarkCalculationModulesUtil = null;
        $classificationUtil = $annualMarkCalculationAllUtil = null;
        $annualMarkCalculationGeneralAverageUtil = $annualMarkCalculationAllUtil = null;

        // Initialisation des services pour la moyenne generale
        $annualMarkGenerationGeneralAverageUtil = new AnnualMarkGenerationGeneralAverageUtil($finalAverageFormula,$schoolSystem,$evaluationPeriods,$this->markPeriodCourseCalculatedRepository,$this->markAnnualCourseCalculatedRepository,$this->entityManager,$annualMarkGenerationUtil);

        $workRemarksAnnualUtil = new WorkRemarksAnnualUtil($blameWorkAverage, $warningWorkAverage, $grantEncouragementAverage, $grantCongratulationAverage, $grantThAnnualAverage);

        $annualMarkCalculationAllUtil = null;

        switch ($schoolSystem) {
            case 'primary':
                // On met primary met ce sont les meme services pour les deux (Primaire et Secondaire)

                // Initialisation des services pour les matieres
                $annualMarkGenerationCourseUtil = new AnnualMarkGenerationCourseUtil($finalMarkFormula,$this->entityManager, $annualMarkGenerationUtil);
                $annualMarkGenerationModuleUtil = new AnnualMarkGenerationModuleUtil($this->entityManager, $annualMarkGenerationUtil);

                $annualMarkCalculationCourseUtil = new AnnualMarkCalculationCoursePrimaryAndSecondaryUtil($finalMarkFormula,$assign0WhenTheMarkIsNotEntered,$isMarkForAllSequenceRequired,$evaluationPeriodsArray,$evaluationPeriods,$this->studentCourseRegistrationRepository,$this->evaluationPeriodRepository,$this->markSequenceCourseCalculatedRepository,$this->markPeriodCourseCalculatedRepository);
                $annualMarkCalculationCoursesUtil = new AnnualMarkCalculationCoursesPrimaryAndSecondaryUtil($finalMarkFormula,$validationBase,$evaluationPeriodsArray,$annualMarkGenerationCourseUtil,$annualMarkCalculationCourseUtil,$this->evaluationPeriodRepository,$this->markSequenceGeneralAverageCalculatedRepository,$this->markPeriodGeneralAverageCalculatedRepository);

                // Initialisation des services pour les modules
                $annualMarkCalculationModuleUtil = new AnnualMarkCalculationModulePrimaryAndSecondaryUtil($assign0WhenTheMarkIsNotEntered,$isCoefficientOfNullMarkConsiderInTheAverageCalculation,$isValidateCompensateModulate,$validationBase,$activateEliminationMarks,$groupEliminateAverage,$this->moduleRepository);
                $annualMarkCalculationModulesUtil = new AnnualMarkCalculationModulesPrimaryAndSecondaryUtil($validationBase,$groupEliminateAverage,$annualMarkGenerationModuleUtil,$annualMarkCalculationModuleUtil);

                $classificationUtil = new ClassificationAnnualPrimaryAndSecondaryUtil($calculateAveragesForUnclassified,$percentageSubjectNumber, $andOr, $percentageTotalCoefficient,$evaluationPeriods,$this->studentCourseRegistrationRepository,$this->markPeriodCourseCalculatedRepository);

                $annualMarkCalculationGeneralAverageUtil = new AnnualMarkCalculationGeneralAveragePrimaryAndSecondaryUtil($assign0WhenTheMarkIsNotEntered = false,$isCoefficientOfNullMarkConsiderInTheAverageCalculation,$generalEliminateAverage,$calculateSubjectsRanks,$activateEliminationMarks,$isEliminationModuleActivated = true,$finalAverageFormula,$dontClassifyTheExcluded,$class,$annualMarkGenerationGeneralAverageUtil, $classificationUtil,$workRemarksAnnualUtil,$this->classProgramRepository,$this->markAnnualCourseCalculatedRepository,$this->markAnnualGeneralAverageCalculatedRepository,$this->entityManager);
                $annualMarkCalculationAllUtil = new AnnualMarkCalculationAllPrimaryAndSecondaryUtil($this->markAnnualGeneralAverageCalculatedRepository,$annualMarkCalculationCoursesUtil, $annualMarkCalculationModulesUtil,$annualMarkCalculationGeneralAverageUtil,$this->entityManager);
                break;

            case 'ternary':
                // Initialisation des services pour les matieres
                $annualMarkCalculationCourseUtil = new AnnualMarkCalculationCourseTernaryUtil($evaluationPeriods,$this->studentCourseRegistrationRepository,$this->markPeriodCourseCalculatedRepository);
                $annualMarkCalculationCoursesUtil = new AnnualMarkCalculationCoursesTernaryUtil($finalAverageFormula,$evaluationPeriodsArray,$annualMarkCalculationCourseUtil,$this->evaluationPeriodRepository,$this->markSequenceGeneralAverageCalculatedRepository,$this->markPeriodGeneralAverageCalculatedRepository);
                $classificationUtil = new ClassificationAnnualTernaryUtil($calculateAveragesForUnclassified,$percentageSubjectNumber, $andOr, $percentageTotalCoefficient,$this->markPeriodCourseCalculatedRepository);
                $annualMarkCalculationGeneralAverageUtil = new AnnualMarkCalculationGeneralAverageTernaryUtil($assign0WhenTheMarkIsNotEntered = false,$isCoefficientOfNullMarkConsiderInTheAverageCalculation,$generalEliminateAverage,$activateEliminationMarks,$isEliminationModuleActivated = true,$finalAverageFormula,$dontClassifyTheExcluded,$class,$annualMarkGenerationGeneralAverageUtil, $classificationUtil,$workRemarksAnnualUtil,$this->markAnnualGeneralAverageCalculatedRepository,$this->entityManager);
                $annualMarkCalculationAllUtil = new AnnualMarkCalculationAllTernaryUtil($this->markAnnualGeneralAverageCalculatedRepository,$annualMarkCalculationCoursesUtil,$annualMarkCalculationGeneralAverageUtil,$this->entityManager);
                break;
        }

        $annualMarkCalculationAllUtil->calculateAnnualAllMarks($case,$element);

        return 'success';
//        return $this->json('success',200,self::header);
    }
}