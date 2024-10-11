<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\GeneralAverage;

// Classe contenant toutes les fonctions concernant le calcul des notes pour une sequence
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\AnnualMarkCalculationUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Classification\ClassificationAnnualPrimaryAndSecondaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Classification\ClassificationAnnualTernaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\WorkRemarksAnnualUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Generation\Student\GeneralAverage\AnnualMarkGenerationGeneralAverageUtil;
use App\Entity\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCalculated;
use App\Entity\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculated;
use App\Entity\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculated;
use App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculated;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Repository\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCalculatedRepository;
use Doctrine\ORM\EntityManagerInterface;

// Il serait preferable de creer un sequence mark calculation pour chaque type (Matiere,Module,Moyenne generale)
class AnnualMarkCalculationGeneralAverageTernaryUtil extends AnnualMarkCalculationUtil
{
    private bool $isEliminationGeneralAverageActivated;

    private string $calculateGeneralAverageMethod;

    private \Closure $dontClassifyTheExcludedFn;

    public function __construct(
        // Configurations
        // Calc des notes d'une matiere
        protected bool                                       $assign0WhenTheMarkIsNotEntered,
        protected bool                                       $isCoefficientOfNullMarkConsiderInTheAverageCalculation,
        private ?float                                       $generalEliminateAverage,

        // Calc de la moyenne generale d'une matiere
        protected bool                                       $activateEliminationMarks,
        private bool                                         $isEliminationModuleActivated,
        private string                                       $finalYearAverageFormula,

        // Autres ou commun au deux
        protected bool                                       $dontClassifyTheExcluded,

        // Attributs principaux
        private readonly SchoolClass                         $class,


        // Utils
        private AnnualMarkGenerationGeneralAverageUtil       $annualMarkGenerationGeneralAverageUtil,
        private ClassificationAnnualTernaryUtil | ClassificationAnnualPrimaryAndSecondaryUtil              $classificationUtil,
        private WorkRemarksAnnualUtil                        $workRemarksAnnualUtil,

        // Repository
        private MarkAnnualGeneralAverageCalculatedRepository $markAnnualGeneralAverageCalculatedRepository,

        // Manager
        private readonly EntityManagerInterface              $entityManager
    )
    {
        parent::__construct($this->isCoefficientOfNullMarkConsiderInTheAverageCalculation);

        $this->isEliminationGeneralAverageActivated = $this->activateEliminationMarks && isset($this->generalEliminateAverage);//$this->activateEliminationMarks && $eliminateAverage;
//        dd($this->isEliminationGeneralAverageActivated);
        // La methode de calcul des notes est la meme que celle du module car il s'agit d'un calcul pondere de notes juste que l'ensemble de notes differe

//        dd($this->isEliminationGeneralAverageActivated());
        $this->calculateMethod = $this->finalYearAverageFormula === '1' ? 'calculateAnnualWeightingAverageIsCoeff' . (int)($this->isCoefficientOfNullMarkConsiderInTheAverageCalculation) . 'Assign' . (int)($this->assign0WhenTheMarkIsNotEntered) . 'Activate' . (int)($this->isEliminationModuleActivated) :
            'calculateAnnualMeanAverage';; // calculateSequenceWeightingAverageIsCoeff0Assign0Activate0
        $this->calculateGeneralAverageMethod = 'calculateAnnualGeneralAverageEvery' . (int)($this->classificationUtil->isEveryBodyClassed()) . 'ElimGen' . (int)($this->isEliminationGeneralAverageActivated);
//        dd($this->calculateGeneralAverageMethod);

        $this->dontClassifyTheExcludedFn =
            fn(array $markCalculateds) => array_values(array_filter($markCalculateds,
                function (mixed $markCalculated) {
                    $status = $markCalculated->getStudent()->getStatus();
                    $isResigned = $status === 'resigned';
                    if ($isResigned) {
                        $markCalculated->setIsClassed(false);
                        $markCalculated->setRank(null);
                    }
                    return !$isResigned;
                }
            ));
    }

    public function isEliminationGeneralAverageActivated(): bool
    {
        return $this->isEliminationGeneralAverageActivated;
    }

    public function setIsEliminationGeneralAverageActivated(bool $isEliminationGeneralAverageActivated): AnnualMarkCalculationGeneralAverageTernaryUtil
    {
        $this->isEliminationGeneralAverageActivated = $isEliminationGeneralAverageActivated;
        return $this;
    }

    public function getEliminateAverage(): float
    {
        return $this->generalEliminateAverage;
    }

    public function setEliminateAverage(float $eliminateAverage): AnnualMarkCalculationGeneralAverageTernaryUtil
    {
        $this->generalEliminateAverage = $eliminateAverage;
        return $this;
    }

    public function getAnnualMarkGenerationGeneralAverageUtil(): AnnualMarkGenerationGeneralAverageUtil
    {
        return $this->annualMarkGenerationGeneralAverageUtil;
    }

    public function setAnnualMarkGenerationGeneralAverageUtil(AnnualMarkGenerationGeneralAverageUtil $annualMarkGenerationGeneralAverageUtil): AnnualMarkCalculationGeneralAverageTernaryUtil
    {
        $this->annualMarkGenerationGeneralAverageUtil = $annualMarkGenerationGeneralAverageUtil;
        return $this;
    }

    // Fonctions de calculs
    // Calculer la moyenne generale d'une periode (classification et eliminations)
    function calculateAnnualGeneralAverage(array $markCalculateds, array $studentCourseRegistrationsAttended): MarkAnnualGeneralAverageCalculated
    {
        $markAnnualGeneralAverageCalculated = $this->{$this->calculateGeneralAverageMethod}($markCalculateds, $studentCourseRegistrationsAttended);
        if ($markAnnualGeneralAverageCalculated->getIsEliminated()) $markAnnualGeneralAverageCalculated->setTotalCreditConsidered(0);
        $this->entityManager->flush();

        // Mettre les remarques du travail
        $this->workRemarksAnnualUtil->setRemarks($markAnnualGeneralAverageCalculated);

        // Mettre les rangs sur les matieres
        /*$classProgramIds = $this->markAnnualCourseCalculatedRepository->getClassPrograms($this->class,$this->evaluationPeriod);
        $classPrograms = array_map(fn(int $classProgramId)=>$this->classProgramRepository->find($classProgramId),array_column($classProgramIds,'classProgramId'));
        foreach ($classPrograms as $classProgram) {
            $markAnnualCourseRankingCalculateds = $this->markAnnualCourseCalculatedRepository->findBy(['classProgram' => $classProgram], ['mark' => 'DESC']);
            $totalCourseStudentsRegistered = count($markAnnualCourseRankingCalculateds);
            foreach ($markAnnualCourseRankingCalculateds as $index => $markAnnualCourseRankingCalculated) {
                $markAnnualCourseRankingCalculated->setCourseRank($index+1);
                $markAnnualCourseRankingCalculated->setTotalCourseStudentsRegistered($totalCourseStudentsRegistered);
            }
        }*/

        // Mettre les rangs sur les moyennes generales
        $markAnnualGeneralAverageCalculateds = $this->markAnnualGeneralAverageCalculatedRepository->findBy(['class' => $this->class, 'isClassed' => true], ['average' => 'DESC']);
        if ($this->dontClassifyTheExcluded) $markAnnualGeneralAverageCalculateds = ($this->dontClassifyTheExcludedFn)($markAnnualGeneralAverageCalculateds);
        $totalStudentsClassed = count($markAnnualGeneralAverageCalculateds);
        foreach ($markAnnualGeneralAverageCalculateds as $index => $markAnnualGeneralAverageRankingCalculated) {
            $markAnnualGeneralAverageRankingCalculated->setRank($index + 1);
            $markAnnualGeneralAverageRankingCalculated->setTotalStudentsClassed($totalStudentsClassed);
        }

        $this->entityManager->flush();
        return $markAnnualGeneralAverageCalculated;
    }

    // Calculer la moyenne generale d'une periode
    // $isEveryBodyClassed = false
    // $isEliminationGeneralAverageActivated = false|true
    function calculateAnnualGeneralAverageEvery0(array $markCalculateds, array $studentCourseRegistrationsAttended): MarkAnnualGeneralAverageCalculated
    {
        $markAnnualGeneralAverageCalculated = $this->calculateAnnualGeneralAverageEvery1($markCalculateds, $studentCourseRegistrationsAttended);
        $isClassed = $markAnnualGeneralAverageCalculated->getIsClassed();
        if (!$isClassed) {
            $markAnnualGeneralAverageCalculated->setAverage(null);
            $markAnnualGeneralAverageCalculated->setAverageGpa(null);
            $markAnnualGeneralAverageCalculated->setIsClassed(false);
        }
        return $markAnnualGeneralAverageCalculated;
    }

    // Calculer la moyenne generale d'une periode
    // $isEveryBodyClassed = false
    // $isEliminationGeneralAverageActivated = false
    // 0 0
    function calculateAnnualGeneralAverageEvery0ElimGen0(array $markCalculateds, array $studentCourseRegistrationsAttended): MarkAnnualGeneralAverageCalculated
    {
        $markAnnualGeneralAverageCalculated = $this->calculateAnnualGeneralAverageEvery0($markCalculateds, $studentCourseRegistrationsAttended);
        return $markAnnualGeneralAverageCalculated;
    }

    // $isEveryBodyClassed = false
    // $isEliminationGeneralAverageActivated = true
    // 0 1
    function calculateAnnualGeneralAverageEvery0ElimGen1(array $markCalculateds, array $studentCourseRegistrationsAttended): MarkAnnualGeneralAverageCalculated
    {
        $markAnnualGeneralAverageCalculated = $this->calculateAnnualGeneralAverageEvery0($markCalculateds, $studentCourseRegistrationsAttended);
        $average = $markAnnualGeneralAverageCalculated->getAverage();
        $markAnnualGeneralAverageCalculated->setIsEliminated(isset($average) && $average < $this->generalEliminateAverage);
        return $markAnnualGeneralAverageCalculated;
    }

    // Calculer la moyenne generale d'une periode
    // $isEveryBodyClassed = true
    // $isEliminationGeneralAverageActivated = false|true
    function calculateAnnualGeneralAverageEvery1(array $markCalculateds, array $studentCourseRegistrationsAttended): MarkAnnualGeneralAverageCalculated
    {
        // Calcul de la moyenne generale et enregistrements
        $classificationDatas = $this->classificationUtil->isClassedWithCourses($studentCourseRegistrationsAttended, $markCalculateds);
        extract($classificationDatas);
        $markDatas = empty($markCalculateds) ? null : $this->calculateMark($markCalculateds);
        $average = null;
        $averageGpa = null;
        if (is_array($markDatas)) {
            $average = $markDatas['average'];
            $averageGpa = $markDatas['averageGpa'];
        } else if (is_float($markDatas)) {
            $average = $markDatas;
        }
        $markAnnualGeneralAverageCalculated = $this->annualMarkGenerationGeneralAverageUtil->generateAnnualGeneralAverageCalculated($average, $averageGpa, $numberOfAttendedCourses, $numberOfComposedCourses, $totalOfCreditsAttended, $totalOfCreditsComposed,
            $percentageSubjectNumber,
            $percentageTotalCoefficient,
            $isClassed,
            $markCalculateds);

        return $markAnnualGeneralAverageCalculated;
    }

    // $isEveryBodyClassed = true
    // $isEliminationGeneralAverageActivated = false
    // 1 0
    function calculateAnnualGeneralAverageEvery1ElimGen0(array $markCalculateds, array $studentCourseRegistrationsAttended): MarkAnnualGeneralAverageCalculated
    {
        $markAnnualGeneralAverageCalculated = $this->calculateAnnualGeneralAverageEvery1($markCalculateds, $studentCourseRegistrationsAttended);
        return $markAnnualGeneralAverageCalculated;
    }

    // $isEveryBodyClassed = true
    // $isEliminationGeneralAverageActivated = true
    // 1 1
    function calculateAnnualGeneralAverageEvery1ElimGen1(array $markCalculateds, array $studentCourseRegistrationsAttended): MarkAnnualGeneralAverageCalculated
    {
        $markAnnualGeneralAverageCalculated = $this->calculateAnnualGeneralAverageEvery1($markCalculateds, $studentCourseRegistrationsAttended);
        $average = $markAnnualGeneralAverageCalculated->getAverage();
        $markAnnualGeneralAverageCalculated->setIsEliminated(isset($average) && $average < $this->generalEliminateAverage);
        return $markAnnualGeneralAverageCalculated;
    }

    // Calcul de la moyenne generale en fonction des configurations
    // halfYearAverageFormula = '1'
    // Meme que celle du module car il s'agit d'une moyenne ponderee de notes

    // Modules et moyenne generale

    // On reecrit toutes les methodes de calculateWeightingAverage en fct des parametres
    // Gain en optimisation car les parametres ne sont plus reevaluees pour chaque appel de la methode mere
    // qui sera remplace par ces methodes filles reecrites pour chaque circonstance (isCoeff = true,assign = false,etc...) (Module,General,Periode de matiere)
    // La methode de base redefinit partout est GeneralUtil::calculateWeightingAverage

    // Moyenne sequentielle de modules & Moyenne periodique de matiere & moyenne generale de sequence
    // Elles se calculent toute a partir des notes sequentielles de matieres calculees


    // static function calculateWeightingAverage(array $marks, bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation, bool $assign0WhenTheMarkIsNotEntered, bool $activateEliminationMarks): float|null

    // Le nom des fonctions filles est de la forme calculatePeriodModuleWeightingAverageIsCoeff $isCoefficient... Assign $assign... Activate $activate...

    /**
     * @param array $markAnnualCoursesCalculated
     * @return array
     */
    public function getNotEliminatedMarks(array $markAnnualCoursesCalculated): array
    {
        return array_filter($markAnnualCoursesCalculated, fn(MarkPeriodCourseCalculated $markAnnualCourseCalculated) => !$markAnnualCourseCalculated->getIsModuleEliminated());
    }


    /**
     * @param array $markAnnualCoursesCalculated
     * @return array
     */
    public function getNotNullMarks(array $markAnnualCoursesCalculated): array
    {
        return array_values(array_filter($markAnnualCoursesCalculated, fn(MarkPeriodCourseCalculated $markAnnualCourseCalculated) => $markAnnualCourseCalculated->getMark() !== null));
    }

    /**
     * @param array $marks
     * @param array $creditValues
     * @return float
     */
    public function getWeightingAverage(array $notNullMarkPeriodCoursesCalculated, bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation): array
    {

        $totalCredit = $total = 0;
        $totalGrade = 0;

        if ($isCoefficientOfNullMarkConsiderInTheAverageCalculation) {
            foreach ($notNullMarkPeriodCoursesCalculated as $markAnnualCourseCalculated) {
                $credit = $markAnnualCourseCalculated->getClassProgram()->getCoeff();
                $markGrade = $markAnnualCourseCalculated->getGrade();
                $mark = $markAnnualCourseCalculated->getMark();
                $total += floatval($mark) * $credit;
                if (isset($markGrade, $totalGrade)) $totalGrade = floatval($totalGrade) + floatval($markGrade->getGpa()) * $credit;
                else $totalGrade = null;
                $totalCredit += $credit;
            }
        } else {
            foreach ($notNullMarkPeriodCoursesCalculated as $markAnnualCourseCalculated) {
                $credit = $markAnnualCourseCalculated->getClassProgram()->getCoeff();
                $markGrade = $markAnnualCourseCalculated->getGrade();
                $mark = floatval($markAnnualCourseCalculated->getMark());
                $total += $mark * $credit;
                if (isset($markGrade, $totalGrade)) $totalGrade = floatval($totalGrade) + floatval($markGrade->getGpa()) * $credit;
                else $totalGrade = null;
                if ($mark != 0) $totalCredit += $credit;
            }
        }

//        dd($total,$totalCredit);
        $average = $averageGpa = null;
        if ($totalCredit !== 0) {
            $average = round(floatval($total / $totalCredit), 2);
            $averageGpa = isset($totalGrade) ? round(floatval($totalGrade / $totalCredit), 2) : null;
        }
        return ['average' => $average, 'averageGpa' => $averageGpa];
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = false
    // assign0WhenTheMarkIsNotEntered = false
    // $activateEliminationMarks = false
    // 0 0 0
    function calculateAnnualWeightingAverageIsCoeff0Assign0Activate0(array $markAnnualCoursesCalculated): array|float|null
    {

        $notNullMarkPeriodCoursesCalculated = $this->getNotNullMarks($markAnnualCoursesCalculated);
        if (empty($markAnnualCoursesCalculated)) {
            return null;
        }

        // Maintenant si toutes les notes restantes sont nulles => moyenne nulle
        if (empty($notNullMarkPeriodCoursesCalculated)) {
            return null;
        } else if (count($notNullMarkPeriodCoursesCalculated) != count($markAnnualCoursesCalculated)) {
            return null;
        }

        return $this->getWeightingAverage($notNullMarkPeriodCoursesCalculated, false);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = false
    // assign0WhenTheMarkIsNotEntered = false
    // $activateEliminationMarks = true
    // 0 0 1
    function calculateAnnualWeightingAverageIsCoeff0Assign0Activate1(array $markAnnualCoursesCalculated): array|float|null
    {
        // On retire les notes eliminees
        $markAnnualCoursesCalculated = array_filter($markAnnualCoursesCalculated, fn(MarkPeriodCourseCalculated $markAnnualCourseCalculated) => $markAnnualCourseCalculated->getIsModuleEliminated());
        if (empty($markAnnualCoursesCalculated)) return null;

        // On renvoie la valeur de la fonction precedente car les traitements sont les memes juste que on retire les notes eliminees
        return $this->calculateAnnualWeightingAverageIsCoeff0Assign0Activate0($markAnnualCoursesCalculated);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = false
    // assign0WhenTheMarkIsNotEntered = true
    // $activateEliminationMarks = false
    // 0 1 0
    function calculateAnnualWeightingAverageIsCoeff0Assign1Activate0(array $markAnnualCoursesCalculated): array|float|null
    {

        if ($this->assign0WhenTheMarkIsNotEntered) {
            foreach ($markAnnualCoursesCalculated as $markAnnualCourseCalculated) {
                if ($markAnnualCourseCalculated->getMark() === null) $markAnnualCourseCalculated->setMark(0);
            }
        }
        $notNullMarkPeriodCoursesCalculated = $this->getNotNullMarks($markAnnualCoursesCalculated);

        // Maintenant si toutes les notes restantes sont nulles => moyenne nulle
        if (empty($notNullMarkPeriodCoursesCalculated)) {
            return null;
        }

        return $this->getWeightingAverage($notNullMarkPeriodCoursesCalculated, false);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = false
    // assign0WhenTheMarkIsNotEntered = true
    // $activateEliminationMarks = true
    // 0 1 1
    function calculateAnnualWeightingAverageIsCoeff0Assign1Activate1(array $markAnnualCoursesCalculated): array|float|null
    {
        if ($this->assign0WhenTheMarkIsNotEntered) {
            foreach ($markAnnualCoursesCalculated as $markAnnualCourseCalculated) {
                if ($markAnnualCourseCalculated->getMark() === null) $markAnnualCourseCalculated->setMark(0);
            }
        }
        // On retire les notes eliminees
        $markAnnualCoursesCalculated = $this->getNotEliminatedMarks($markAnnualCoursesCalculated);
        if (empty($markAnnualCoursesCalculated)) return null;

        // On renvoie la valeur de la fonction precedente car les traitements sont les memes juste que on retire les notes eliminees
        return $this->calculateAnnualWeightingAverageIsCoeff0Assign1Activate0($markAnnualCoursesCalculated);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = true
    // assign0WhenTheMarkIsNotEntered = false
    // $activateEliminationMarks = false
    // 1 0 0
    function calculateAnnualWeightingAverageIsCoeff1Assign0Activate0(array $markAnnualCoursesCalculated): array|float|null
    {
        // Le resultat est le meme que le cas 0 0 0 car assign0WhenTheMarkIsNotEntered = false
        return $this->calculateAnnualWeightingAverageIsCoeff0Assign0Activate0($markAnnualCoursesCalculated);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = true
    // assign0WhenTheMarkIsNotEntered = false
    // $activateEliminationMarks = true
    // 1 0 1
    function calculateAnnualWeightingAverageIsCoeff1Assign0Activate1(array $markAnnualCoursesCalculated): array|float|null
    {
        // On retire les notes eliminees
        $markAnnualCoursesCalculated = $this->getNotEliminatedMarks($markAnnualCoursesCalculated);
        if (empty($markAnnualCoursesCalculated)) return null;
        return $this->calculateAnnualWeightingAverageIsCoeff0Assign0Activate0($markAnnualCoursesCalculated);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = true
    // assign0WhenTheMarkIsNotEntered = true
    // $activateEliminationMarks = false
    // 1 1 0
    function calculateAnnualWeightingAverageIsCoeff1Assign1Activate0(array $markAnnualCoursesCalculated): array|float|null
    {
        if ($this->assign0WhenTheMarkIsNotEntered) {
            foreach ($markAnnualCoursesCalculated as $markAnnualCourseCalculated) {
                if ($markAnnualCourseCalculated->getMark() === null) $markAnnualCourseCalculated->setMark(0);
            }
        }

        $notNullMarkPeriodCoursesCalculated = $this->getNotNullMarks($markAnnualCoursesCalculated);

        // Maintenant si toutes les notes restantes sont nulles => moyenne nulle
        if (empty($notNullMarkPeriodCoursesCalculated)) {
            return 0;
        }

        return $this->getWeightingAverage($notNullMarkPeriodCoursesCalculated, true);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = true
    // assign0WhenTheMarkIsNotEntered = true
    // $activateEliminationMarks = true
    // 1 1 1
    function calculateAnnualWeightingAverageIsCoeff1Assign1Activate1(array $markAnnualCoursesCalculated): array|float|null
    {
        if ($this->assign0WhenTheMarkIsNotEntered) {
            foreach ($markAnnualCoursesCalculated as $markAnnualCourseCalculated) {
                if ($markAnnualCourseCalculated->getMark() === null) $markAnnualCourseCalculated->setMark(0);
            }
        }
        // On retire les notes eliminees
        $markAnnualCoursesCalculated = $this->getNotEliminatedMarks($markAnnualCoursesCalculated);
        if (empty($markAnnualCoursesCalculated)) return null;
        return $this->calculateAnnualWeightingAverageIsCoeff1Assign1Activate0($markAnnualCoursesCalculated);
    }

    // halfYearAverageFormula = '2'
    // assign0WhenTheMarkIsNotEntered = false
    // isMarkForAllSequenceRequired = true
    // Ici, si une moyenne est absente , on ne calcule pas la moyenne generale
    function calculateAnnualMeanAverage(array $markGeneralAverageCalculateds)
    {
        // Verification
        // Recuperer les notes ($markSequenceCourseCalculateds)
        $numberOfGeneralAverages = count($markGeneralAverageCalculateds);

        if (!$numberOfGeneralAverages) return null;

        // Recuperer les notes generees
        $markGeneralAverageCalculatedsGenerated = [];
        foreach ($markGeneralAverageCalculateds as $markGeneralAverageCalculated) {
            if ($markGeneralAverageCalculated) $markGeneralAverageCalculatedsGenerated[] = $markGeneralAverageCalculated;
        }
        $numberOfGeneralAveragesGenerated = count($markGeneralAverageCalculatedsGenerated);

        // S'il y a une note non generee
        if ($numberOfGeneralAveragesGenerated !== $numberOfGeneralAverages
            // Si $isMarkForAllSequenceRequired = true , renvoyer null
        ) return null;
        // Sinon retirer cette note
        // On a deja retire

        // Recuperer les notes saisies
        $markGeneralAverageCalculateds = array_filter($markGeneralAverageCalculatedsGenerated, fn(MarkSequenceGeneralAverageCalculated | MarkPeriodGeneralAverageCalculated $markGeneralAverageCalculated) => $markGeneralAverageCalculated->getAverage());
        $numberOfGeneralAveragesEntered = count($markGeneralAverageCalculateds);

        // S'il y a une note non saisie
        if ($numberOfGeneralAveragesGenerated !== $numberOfGeneralAveragesEntered
            // Si $assign0WhenTheMarkIsNotEntered = false , renvoyer null
        ) return null;
        // Sinon continuer, on manage ces cas plus tard

        // Calculer la note suivant la formule
        $sum = 0;
        $totalGrade = 0;
        foreach ($markGeneralAverageCalculateds as $markGeneralAverageCalculated) {
            $average = $markGeneralAverageCalculated->getAverage();
            $grade = $markGeneralAverageCalculated->getGrade();
            if (isset($grade, $totalGrade)) $totalGrade += $grade->getGpa();
            else $totalGrade = null;
            $sum += floatval($average);
        }
        $average = round(floatval($sum / $numberOfGeneralAveragesEntered), 2);
        $averageGpa = $totalGrade ? round(floatval($totalGrade / $numberOfGeneralAveragesEntered), 2) : null;
        return ['average' => $average, 'averageGpa' => $averageGpa];
    }


}