<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Module\PrimaryAndSecondary;

// Classe contenant toutes les fonctions concernant le calcul des notes pour une sequence
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\AnnualMarkCalculationUtil;
use App\Entity\School\Exam\Operation\Annual\Course\MarkAnnualCourseCalculated;
use App\Repository\School\Study\Configuration\ModuleRepository;

// Il serait preferable de creer un sequence mark calculation pour chaque type (Matiere,Module,Moyenne generale)
class AnnualMarkCalculationModulePrimaryAndSecondaryUtil extends AnnualMarkCalculationUtil
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
        $this->isEliminable = $this->activateEliminationMarks && isset($this->groupEliminateAverage);
        $this->isModulable = $this->isValidateCompensateModulate && isset($this->validationBase);
        $this->calculateMethod = 'calculateAnnualWeightingAverageIsCoeff'.(int)($this->isCoefficientOfNullMarkConsiderInTheAverageCalculation).'Assign'.(int)($this->assign0WhenTheMarkIsNotEntered).'Activate'.(0); // calculateAnnualWeightingAverageIsCoeff0Assign0Activate0
    }

    public function isEliminable(): bool
    {
        return $this->isEliminable;
    }

    public function setIsEliminable(bool $isEliminable): AnnualMarkCalculationModulePrimaryAndSecondaryUtil
    {
        $this->isEliminable = $isEliminable;
        return $this;
    }


    public function getValidationBase(): ?float
    {
        return $this->validationBase;
    }

    public function setValidationBase(?float $validationBase): AnnualMarkCalculationModulePrimaryAndSecondaryUtil
    {
        $this->validationBase = $validationBase;
        return $this;
    }

    public function isModulable(): bool
    {
        return $this->isModulable;
    }

    public function setIsModulable(bool $isModulable): AnnualMarkCalculationModulePrimaryAndSecondaryUtil
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
    // qui sera remplace par ces methodes filles reecrites pour chaque circonstance (isCoeff = true,assign = false,etc...) (Module,General,Annuale de matiere)
    // La methode de base redefinie partout est GeneralUtil::calculateWeightingAverage

    // Moyenne sequentielle de modules & Moyenne periodique de matiere & moyenne generale de sequence
    // Elles se calculent toute a partir des notes sequentielles de matieres calculees


    // static function calculateWeightingAverage(array $marks, bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation, bool $assign0WhenTheMarkIsNotEntered, bool $activateEliminationMarks): float|null

    // Le nom des fonctions filles est de la forme calculateAnnualModuleWeightingAverageIsCoeff $isCoefficient... Assign $assign... Activate $activate...

    /**
     * @param array $markAnnualCoursesCalculated
     * @return array
     */
    public function getNotEliminatedMarks(array $markAnnualCoursesCalculated): array
    {
        return array_filter($markAnnualCoursesCalculated, fn(MarkAnnualCourseCalculated $markAnnualCourseCalculated) => !$markAnnualCourseCalculated->getIsEliminated());
    }

    /**
     * @param array $markAnnualCoursesCalculated
     * @return array
     */
    public function getNotNullMarks(array $markAnnualCoursesCalculated): array
    {
        return array_values(array_filter($markAnnualCoursesCalculated, fn(MarkAnnualCourseCalculated $markAnnualCourseCalculated) => $markAnnualCourseCalculated->getMark() !== null));
    }

    /**
     * @param array $marks
     * @param array $creditValues
     * @return float
     */
    public function getWeightingAverage(array $notNullMarkAnnualCoursesCalculated,bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation): array
    {

        $totalCredit = $total = 0;
        $totalGrade = 0;

        if ($isCoefficientOfNullMarkConsiderInTheAverageCalculation) { 
            foreach ($notNullMarkAnnualCoursesCalculated as $markAnnualCourseCalculated) {
                $credit = $markAnnualCourseCalculated->getClassProgram()->getCoeff();
                $markGrade = $markAnnualCourseCalculated->getGrade();
                $mark = $markAnnualCourseCalculated->getMark();
                $total += floatval($mark) * $credit;
                if (isset($markGrade) && isset($totalGrade)){
                    $totalGrade = floatval($totalGrade) + floatval($markGrade->getGpa()) * $credit;
                }
                else $totalGrade = null;
                $totalCredit += $credit;
            }
        } else {
            foreach ($notNullMarkAnnualCoursesCalculated as $markAnnualCourseCalculated) {
                $credit = $markAnnualCourseCalculated->getClassProgram()->getCoeff();
                $markGrade = $markAnnualCourseCalculated->getGrade();
                $mark = floatval($markAnnualCourseCalculated->getMark());
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
    function calculateAnnualWeightingAverageIsCoeff0Assign0Activate0(array $markAnnualCoursesCalculated): array|float|null
    {

        $notNullMarkAnnualCoursesCalculated = $this->getNotNullMarks($markAnnualCoursesCalculated);
        if (empty($markAnnualCoursesCalculated)) { return null;}

        // Maintenant si toutes les notes restantes sont nulles => moyenne nulle
        if (empty($notNullMarkAnnualCoursesCalculated)) {
            return null;
        }
        else if (count($notNullMarkAnnualCoursesCalculated) != count($markAnnualCoursesCalculated)){
            return null;
        }

        return $this->getWeightingAverage($notNullMarkAnnualCoursesCalculated,false);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = false
    // assign0WhenTheMarkIsNotEntered = false
    // $activateEliminationMarks = true
    // 0 0 1
    function calculateAnnualWeightingAverageIsCoeff0Assign0Activate1(array $markAnnualCoursesCalculated): array|float|null {
        // On retire les notes eliminees
        $markAnnualCoursesCalculated = array_filter($markAnnualCoursesCalculated,fn(MarkAnnualCourseCalculated $markAnnualCourseCalculated) => $markAnnualCourseCalculated->getIsEliminated());
        if (empty($markAnnualCoursesCalculated)) return null;

        // On renvoie la valeur de la fonction precedente car les traitements sont les memes juste que on retire les notes eliminees
        return $this->calculateAnnualWeightingAverageIsCoeff0Assign0Activate0($markAnnualCoursesCalculated);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = false
    // assign0WhenTheMarkIsNotEntered = true
    // $activateEliminationMarks = false
    // 0 1 0
    function calculateAnnualWeightingAverageIsCoeff0Assign1Activate0(array $markAnnualCoursesCalculated): array | float | null {
        if ($this->assign0WhenTheMarkIsNotEntered){
            foreach ($markAnnualCoursesCalculated as $markAnnualCourseCalculated) {
                if ($markAnnualCourseCalculated->getMark() === null) $markAnnualCourseCalculated->setMark(0);
            }
        }

        $notNullMarkAnnualCoursesCalculated = $this->getNotNullMarks($markAnnualCoursesCalculated);

        // Maintenant si toutes les notes restantes sont nulles => moyenne nulle
        if (empty($notNullMarkAnnualCoursesCalculated)) {
            return null;
        }

        return $this->getWeightingAverage($notNullMarkAnnualCoursesCalculated,false);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = false
    // assign0WhenTheMarkIsNotEntered = true
    // $activateEliminationMarks = true
    // 0 1 1
    function calculateAnnualWeightingAverageIsCoeff0Assign1Activate1(array $markAnnualCoursesCalculated): array | float | null {
        if ($this->assign0WhenTheMarkIsNotEntered){
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
    function calculateAnnualWeightingAverageIsCoeff1Assign0Activate0(array $markAnnualCoursesCalculated): array | float | null {
        // Le resultat est le meme que le cas 0 0 0 car assign0WhenTheMarkIsNotEntered = false
        return $this->calculateAnnualWeightingAverageIsCoeff0Assign0Activate0($markAnnualCoursesCalculated);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = true
    // assign0WhenTheMarkIsNotEntered = false
    // $activateEliminationMarks = true
    // 1 0 1
    function calculateAnnualWeightingAverageIsCoeff1Assign0Activate1(array $markAnnualCoursesCalculated): array | float | null {
        // On retire les notes eliminees
        $markAnnualCoursesCalculated = $this->getNotEliminatedMarks($markAnnualCoursesCalculated);
        if (empty($markAnnualCoursesCalculated)) return null;
        return $this->calculateAnnualWeightingAverageIsCoeff0Assign0Activate0($markAnnualCoursesCalculated);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = true
    // assign0WhenTheMarkIsNotEntered = true
    // $activateEliminationMarks = false
    // 1 1 0
    function calculateAnnualWeightingAverageIsCoeff1Assign1Activate0(array $markAnnualCoursesCalculated): array | float | null {
        if ($this->assign0WhenTheMarkIsNotEntered){
            foreach ($markAnnualCoursesCalculated as $markAnnualCourseCalculated) {
                if ($markAnnualCourseCalculated->getMark() === null) $markAnnualCourseCalculated->setMark(0);
            }
        }
        $notNullMarkAnnualCoursesCalculated = $this->getNotNullMarks($markAnnualCoursesCalculated);

        // Maintenant si toutes les notes restantes sont nulles => moyenne nulle
        if (empty($notNullMarkAnnualCoursesCalculated)) {
            return 0;
        }

        return $this->getWeightingAverage($notNullMarkAnnualCoursesCalculated,true);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = true
    // assign0WhenTheMarkIsNotEntered = true
    // $activateEliminationMarks = true
    // 1 1 1
    function calculateAnnualWeightingAverageIsCoeff1Assign1Activate1(array $markAnnualCoursesCalculated): array | float | null {
        if ($this->assign0WhenTheMarkIsNotEntered){
            foreach ($markAnnualCoursesCalculated as $markAnnualCourseCalculated) {
                if ($markAnnualCourseCalculated->getMark() === null) $markAnnualCourseCalculated->setMark(0);
            }
        }
        // On retire les notes eliminees
        $markAnnualCoursesCalculated = $this->getNotEliminatedMarks($markAnnualCoursesCalculated);
        if (empty($markAnnualCoursesCalculated)) return null;
        return $this->calculateAnnualWeightingAverageIsCoeff1Assign1Activate0($markAnnualCoursesCalculated);
    }
}