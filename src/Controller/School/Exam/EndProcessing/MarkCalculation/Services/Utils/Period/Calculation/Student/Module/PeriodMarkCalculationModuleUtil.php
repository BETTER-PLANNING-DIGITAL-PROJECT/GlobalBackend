<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Calculation\Student\Module;

// Classe contenant toutes les fonctions concernant le calcul des notes pour une sequence
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Calculation\Student\PeriodMarkCalculationUtil;
use App\Entity\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculated;
use App\Repository\School\Study\Configuration\ModuleRepository;

// Il serait preferable de creer un sequence mark calculation pour chaque type (Matiere,Module,Moyenne generale)
class PeriodMarkCalculationModuleUtil extends PeriodMarkCalculationUtil
{
    private bool $isModulable;
    private bool $isEliminable;

    public function __construct(
        // Configurations
        // Calc des notes d'une matiere
        protected bool     $assign0WhenTheMarkIsNotEntered,
        protected bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation,
        protected bool $isValidateCompensateModulate,
        protected ?float $validationBase,
        protected bool $activateEliminationMarks,
        protected ?float $groupEliminateAverage,
        // Repository
        private  readonly ?ModuleRepository $moduleRepository = null
)
    {
        parent::__construct($this->assign0WhenTheMarkIsNotEntered,$this->isCoefficientOfNullMarkConsiderInTheAverageCalculation);
        $this->isEliminable = $this->activateEliminationMarks && $this->groupEliminateAverage;
        $this->isModulable = $this->isValidateCompensateModulate && $this->validationBase;
        $this->calculateMethod = 'calculatePeriodWeightingAverageIsCoeff'.(int)($this->isCoefficientOfNullMarkConsiderInTheAverageCalculation).'Assign'.(int)($this->assign0WhenTheMarkIsNotEntered).'Activate'.(0); // calculatePeriodWeightingAverageIsCoeff0Assign0Activate0
    }

    public function isEliminable(): bool
    {
        return $this->isEliminable;
    }

    public function setIsEliminable(bool $isEliminable): PeriodMarkCalculationModuleUtil
    {
        $this->isEliminable = $isEliminable;
        return $this;
    }


    public function getValidationBase(): ?float
    {
        return $this->validationBase;
    }

    public function setValidationBase(?float $validationBase): PeriodMarkCalculationModuleUtil
    {
        $this->validationBase = $validationBase;
        return $this;
    }

    public function isModulable(): bool
    {
        return $this->isModulable;
    }

    public function setIsModulable(bool $isModulable): PeriodMarkCalculationModuleUtil
    {
        $this->isModulable = $isModulable;
        return $this;
    }

    public function getModule(int $id)
    {
        return $this->moduleRepository->find($id);
    }

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
     * @param array $markPeriodCoursesCalculated
     * @return array
     */
    public function getNotEliminatedMarks(array $markPeriodCoursesCalculated): array
    {
        return array_filter($markPeriodCoursesCalculated, fn(MarkPeriodCourseCalculated $markPeriodCourseCalculated) => !$markPeriodCourseCalculated->getIsEliminated());
    }

    /**
     * @param array $markPeriodCoursesCalculated
     * @return array
     */
    public function getNotNullMarks(array $markPeriodCoursesCalculated): array
    {
        return array_values(array_filter($markPeriodCoursesCalculated, fn(MarkPeriodCourseCalculated $markPeriodCourseCalculated) => $markPeriodCourseCalculated->getMark() !== null));
    }

    /**
     * @param array $marks
     * @param array $creditValues
     * @return float
     */
    public function getWeightingAverage(array $notNullMarkPeriodCoursesCalculated,bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation): array
    {

        $totalCredit = $total = 0;
        $totalGrade = 0;

        if ($isCoefficientOfNullMarkConsiderInTheAverageCalculation) { 
            foreach ($notNullMarkPeriodCoursesCalculated as $markPeriodCourseCalculated) {
                $credit = $markPeriodCourseCalculated->getClassProgram()->getCoeff();
                $markGrade = $markPeriodCourseCalculated->getGrade();
                $mark = $markPeriodCourseCalculated->getMark();
                $total += floatval($mark) * $credit;
                if (isset($markGrade) && isset($totalGrade)){
                    $totalGrade = floatval($totalGrade) + floatval($markGrade->getGpa()) * $credit;
                }
                else $totalGrade = null;
                $totalCredit += $credit;
            }
        } else {
            foreach ($notNullMarkPeriodCoursesCalculated as $markPeriodCourseCalculated) {
                $credit = $markPeriodCourseCalculated->getClassProgram()->getCoeff();
                $markGrade = $markPeriodCourseCalculated->getGrade();
                $mark = floatval($markPeriodCourseCalculated->getMark());
                $total += $mark * $credit;
                if (isset($markGrade) && isset($totalGrade)){
                    $totalGrade = floatval($totalGrade) + floatval($markGrade->getGpa()) * $credit;
                }
                else $totalGrade = null;
                if($mark != 0) $totalCredit += $credit;
            }
        }

//        dd($total,$totalCredit);
        $mark = $averageGpa = null;
        if ($totalCredit !== 0){
            $mark = round(floatval($total / $totalCredit), 2);
            $averageGpa = isset($totalGrade) ? round(floatval($totalGrade / $totalCredit), 2) : null;
        }
        return ['mark'=>$mark,'averageGpa'=>$averageGpa];
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = false
    // assign0WhenTheMarkIsNotEntered = false
    // $activateEliminationMarks = false
    // 0 0 0
    function calculatePeriodWeightingAverageIsCoeff0Assign0Activate0(array $markPeriodCoursesCalculated): array|float|null
    {

        $notNullMarkPeriodCoursesCalculated = $this->getNotNullMarks($markPeriodCoursesCalculated);
        if (empty($markPeriodCoursesCalculated)) { return null;}

        // Maintenant si toutes les notes restantes sont nulles => moyenne nulle
        if (empty($notNullMarkPeriodCoursesCalculated)) {
            return null;
        }
        else if (count($notNullMarkPeriodCoursesCalculated) != count($markPeriodCoursesCalculated)){
            return null;
        }

        return $this->getWeightingAverage($notNullMarkPeriodCoursesCalculated,false);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = false
    // assign0WhenTheMarkIsNotEntered = false
    // $activateEliminationMarks = true
    // 0 0 1
    function calculatePeriodWeightingAverageIsCoeff0Assign0Activate1(array $markPeriodCoursesCalculated): array|float|null {
        // On retire les notes eliminees
        $markPeriodCoursesCalculated = array_filter($markPeriodCoursesCalculated,fn(MarkPeriodCourseCalculated $markPeriodCourseCalculated) => $markPeriodCourseCalculated->getIsEliminated());
        if (empty($markPeriodCoursesCalculated)) return null;

        // On renvoie la valeur de la fonction precedente car les traitements sont les memes juste que on retire les notes eliminees
        return $this->calculatePeriodWeightingAverageIsCoeff0Assign0Activate0($markPeriodCoursesCalculated);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = false
    // assign0WhenTheMarkIsNotEntered = true
    // $activateEliminationMarks = false
    // 0 1 0
    function calculatePeriodWeightingAverageIsCoeff0Assign1Activate0(array $markPeriodCoursesCalculated): array | float | null {
        if ($this->assign0WhenTheMarkIsNotEntered){
            foreach ($markPeriodCoursesCalculated as $markPeriodCourseCalculated) {
                if ($markPeriodCourseCalculated->getMark() === null) $markPeriodCourseCalculated->setMark(0);
            }
        }

        $notNullMarkPeriodCoursesCalculated = $this->getNotNullMarks($markPeriodCoursesCalculated);

        // Maintenant si toutes les notes restantes sont nulles => moyenne nulle
        if (empty($notNullMarkPeriodCoursesCalculated)) {
            return null;
        }

        return $this->getWeightingAverage($notNullMarkPeriodCoursesCalculated,false);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = false
    // assign0WhenTheMarkIsNotEntered = true
    // $activateEliminationMarks = true
    // 0 1 1
    function calculatePeriodWeightingAverageIsCoeff0Assign1Activate1(array $markPeriodCoursesCalculated): array | float | null {
        if ($this->assign0WhenTheMarkIsNotEntered){
            foreach ($markPeriodCoursesCalculated as $markPeriodCourseCalculated) {
                if ($markPeriodCourseCalculated->getMark() === null) $markPeriodCourseCalculated->setMark(0);
            }
        }
        // On retire les notes eliminees
        $markPeriodCoursesCalculated = $this->getNotEliminatedMarks($markPeriodCoursesCalculated);
        if (empty($markPeriodCoursesCalculated)) return null;

        // On renvoie la valeur de la fonction precedente car les traitements sont les memes juste que on retire les notes eliminees
        return $this->calculatePeriodWeightingAverageIsCoeff0Assign1Activate0($markPeriodCoursesCalculated);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = true
    // assign0WhenTheMarkIsNotEntered = false
    // $activateEliminationMarks = false
    // 1 0 0
    function calculatePeriodWeightingAverageIsCoeff1Assign0Activate0(array $markPeriodCoursesCalculated): array | float | null {
        // Le resultat est le meme que le cas 0 0 0 car assign0WhenTheMarkIsNotEntered = false
        return $this->calculatePeriodWeightingAverageIsCoeff0Assign0Activate0($markPeriodCoursesCalculated);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = true
    // assign0WhenTheMarkIsNotEntered = false
    // $activateEliminationMarks = true
    // 1 0 1
    function calculatePeriodWeightingAverageIsCoeff1Assign0Activate1(array $markPeriodCoursesCalculated): array | float | null {
        // On retire les notes eliminees
        $markPeriodCoursesCalculated = $this->getNotEliminatedMarks($markPeriodCoursesCalculated);
        if (empty($markPeriodCoursesCalculated)) return null;
        return $this->calculatePeriodWeightingAverageIsCoeff0Assign0Activate0($markPeriodCoursesCalculated);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = true
    // assign0WhenTheMarkIsNotEntered = true
    // $activateEliminationMarks = false
    // 1 1 0
    function calculatePeriodWeightingAverageIsCoeff1Assign1Activate0(array $markPeriodCoursesCalculated): array | float | null {
        if ($this->assign0WhenTheMarkIsNotEntered){
            foreach ($markPeriodCoursesCalculated as $markPeriodCourseCalculated) {
                if ($markPeriodCourseCalculated->getMark() === null) $markPeriodCourseCalculated->setMark(0);
            }
        }
        $notNullMarkPeriodCoursesCalculated = $this->getNotNullMarks($markPeriodCoursesCalculated);

        // Maintenant si toutes les notes restantes sont nulles => moyenne nulle
        if (empty($notNullMarkPeriodCoursesCalculated)) {
            return 0;
        }

        return $this->getWeightingAverage($notNullMarkPeriodCoursesCalculated,true);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = true
    // assign0WhenTheMarkIsNotEntered = true
    // $activateEliminationMarks = true
    // 1 1 1
    function calculatePeriodWeightingAverageIsCoeff1Assign1Activate1(array $markPeriodCoursesCalculated): array | float | null {
        if ($this->assign0WhenTheMarkIsNotEntered){
            foreach ($markPeriodCoursesCalculated as $markPeriodCourseCalculated) {
                if ($markPeriodCourseCalculated->getMark() === null) $markPeriodCourseCalculated->setMark(0);
            }
        }
        // On retire les notes eliminees
        $markPeriodCoursesCalculated = $this->getNotEliminatedMarks($markPeriodCoursesCalculated);
        if (empty($markPeriodCoursesCalculated)) return null;
        return $this->calculatePeriodWeightingAverageIsCoeff1Assign1Activate0($markPeriodCoursesCalculated);
    }
}