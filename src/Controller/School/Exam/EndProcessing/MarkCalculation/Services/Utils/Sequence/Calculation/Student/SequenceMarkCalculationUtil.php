<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student;

// Classe contenant toutes les fonctions concernant le calcul des notes pour une sequence
use App\Entity\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculated;

// Il serait preferable de creer un sequence mark calculation pour chaque type (Matiere,Module,Moyenne generale)
class SequenceMarkCalculationUtil
{
    protected string $calculateMethod;
//    protected bool $isEliminationGeneralAverageActivated;
//    protected bool $isEliminationCourseActivated = false;

//    protected bool $activateEliminationMarks;
        // Calc de la moyenne d'un groupe , moyenne generale
//    protected bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation;

    public function __construct(
//        private string $calculationType,

        // Attributs principaux
//        private readonly Sequence $sequence,

        // Configurations
        // Calc des notes d'une matiere
//            private readonly bool           $activateWeightingsPerAssignment,
        protected bool     $assign0WhenTheMarkIsNotEntered,
        protected bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation = false,
    )
    {
    }

    function calculateMark(array $marks){
        return $this->{$this->calculateMethod}($marks);
    }

    // Modules et moyenne generale

    // On reecrit toutes les methodes de calculateWeightingAverage en fct des parametres
    // Gain en optimisation car les parametres ne sont plus reevaluees pour chaque appel de la methode mere
    // qui sera remplace par ces methodes filles reecrites pour chaque circonstance (isCoeff = true,assign = false,etc...) (Module,General,Periode de matiere)
    // La methode de base redefinit partout est GeneralUtil::calculateWeightingAverage

    // Moyenne sequentielle de modules & Moyenne periodique de matiere & moyenne generale de sequence
    // Elles se calculent toute a partir des notes sequentielles de matieres calculees


    // static function calculateWeightingAverage(array $marks, bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation, bool $assign0WhenTheMarkIsNotEntered, bool $activateEliminationMarks): float|null

    // Le nom des fonctions filles est de la forme calculateSequenceModuleWeightingAverageIsCoeff $isCoefficient... Assign $assign... Activate $activate...

    /**
     * @param array $markSequenceCoursesCalculated
     * @return array
     */
    public function getNotEliminatedMarks(array $markSequenceCoursesCalculated): array
    {
        return array_filter($markSequenceCoursesCalculated, fn(MarkSequenceCourseCalculated $markSequenceCourseCalculated) => !$markSequenceCourseCalculated->getIsEliminated());
    }


    /**
     * @param array $markSequenceCoursesCalculated
     * @return array
     */
    public function getNotNullMarks(array $markSequenceCoursesCalculated): array
    {
        return array_values(array_filter($markSequenceCoursesCalculated, fn(MarkSequenceCourseCalculated $markSequenceCourseCalculated) => $markSequenceCourseCalculated->getMark() !== null));
    }

    /**
     * @param array $marks
     * @param array $creditValues
     * @return float
     */
    public function getWeightingAverage(array $notNullMarkSequenceCoursesCalculated,bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation): array
    {

        $totalCredit = $total = 0;
        $totalGrade = 0;

        if ($isCoefficientOfNullMarkConsiderInTheAverageCalculation) {
            foreach ($notNullMarkSequenceCoursesCalculated as $markSequenceCourseCalculated) {
                $credit = $markSequenceCourseCalculated->getClassProgram()->getCoeff();
                $markGrade = $markSequenceCourseCalculated->getGrade();
                $mark = $markSequenceCourseCalculated->getMark();
                $total += floatval($mark) * $credit;
                if (isset($markGrade,$totalGrade)) $totalGrade = floatval($totalGrade) + floatval($markGrade->getGpa()) * $credit;
                else $totalGrade = null;
                $totalCredit += $credit;
            }
        } else {
            foreach ($notNullMarkSequenceCoursesCalculated as $markSequenceCourseCalculated) {
                $credit = $markSequenceCourseCalculated->getClassProgram()->getCoeff();
                $markGrade = $markSequenceCourseCalculated->getGrade();
                $mark = floatval($markSequenceCourseCalculated->getMark());
                $total += $mark * $credit;
                if (isset($markGrade,$totalGrade)) $totalGrade = floatval($totalGrade) + floatval($markGrade->getGpa()) * $credit;
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
    function calculateSequenceWeightingAverageIsCoeff0Assign0Activate0(array $markSequenceCoursesCalculated): array|float|null
    {

        $notNullMarkSequenceCoursesCalculated = $this->getNotNullMarks($markSequenceCoursesCalculated);
        if (empty($markSequenceCoursesCalculated)) { return null;}

        // Maintenant si toutes les notes restantes sont nulles => moyenne nulle
        if (empty($notNullMarkSequenceCoursesCalculated)) {
            return null;
        }
        else if (count($notNullMarkSequenceCoursesCalculated) != count($markSequenceCoursesCalculated)){
            return null;
        }

        return $this->getWeightingAverage($notNullMarkSequenceCoursesCalculated,false);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = false
    // assign0WhenTheMarkIsNotEntered = false
    // $activateEliminationMarks = true
    // 0 0 1
    function calculateSequenceWeightingAverageIsCoeff0Assign0Activate1(array $markSequenceCoursesCalculated): array|float|null {
        // On retire les notes eliminees
        $markSequenceCoursesCalculated = array_filter($markSequenceCoursesCalculated,fn(MarkSequenceCourseCalculated $markSequenceCourseCalculated) => $markSequenceCourseCalculated->getIsEliminated());
        if (empty($markSequenceCoursesCalculated)) return null;

        // On renvoie la valeur de la fonction precedente car les traitements sont les memes juste que on retire les notes eliminees
        return $this->calculateSequenceWeightingAverageIsCoeff0Assign0Activate0($markSequenceCoursesCalculated);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = false
    // assign0WhenTheMarkIsNotEntered = true
    // $activateEliminationMarks = false
    // 0 1 0
    function calculateSequenceWeightingAverageIsCoeff0Assign1Activate0(array $markSequenceCoursesCalculated): array | float | null {

        foreach ($markSequenceCoursesCalculated as $markSequenceCourseCalculated) {
           if ($markSequenceCourseCalculated->getMark() === null) $markSequenceCourseCalculated->setMark(0);
        }
        $notNullMarkSequenceCoursesCalculated = $this->getNotNullMarks($markSequenceCoursesCalculated);

        // Maintenant si toutes les notes restantes sont nulles => moyenne nulle
        if (empty($notNullMarkSequenceCoursesCalculated)) {
            return null;
        }

        return $this->getWeightingAverage($notNullMarkSequenceCoursesCalculated,false);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = false
    // assign0WhenTheMarkIsNotEntered = true
    // $activateEliminationMarks = true
    // 0 1 1
    function calculateSequenceWeightingAverageIsCoeff0Assign1Activate1(array $markSequenceCoursesCalculated): array | float | null {
        foreach ($markSequenceCoursesCalculated as $markSequenceCourseCalculated) {
            if ($markSequenceCourseCalculated->getMark() === null) $markSequenceCourseCalculated->setMark(0);
        }
        // On retire les notes eliminees
        $markSequenceCoursesCalculated = $this->getNotEliminatedMarks($markSequenceCoursesCalculated);
        if (empty($markSequenceCoursesCalculated)) return null;

        // On renvoie la valeur de la fonction precedente car les traitements sont les memes juste que on retire les notes eliminees
        return $this->calculateSequenceWeightingAverageIsCoeff0Assign1Activate0($markSequenceCoursesCalculated);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = true
    // assign0WhenTheMarkIsNotEntered = false
    // $activateEliminationMarks = false
    // 1 0 0
    function calculateSequenceWeightingAverageIsCoeff1Assign0Activate0(array $markSequenceCoursesCalculated): array | float | null {
        // Le resultat est le meme que le cas 0 0 0 car assign0WhenTheMarkIsNotEntered = false
        return $this->calculateSequenceWeightingAverageIsCoeff0Assign0Activate0($markSequenceCoursesCalculated);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = true
    // assign0WhenTheMarkIsNotEntered = false
    // $activateEliminationMarks = true
    // 1 0 1
    function calculateSequenceWeightingAverageIsCoeff1Assign0Activate1(array $markSequenceCoursesCalculated): array | float | null {
        // On retire les notes eliminees
        $markSequenceCoursesCalculated = $this->getNotEliminatedMarks($markSequenceCoursesCalculated);
        if (empty($markSequenceCoursesCalculated)) return null;
        return $this->calculateSequenceWeightingAverageIsCoeff0Assign0Activate0($markSequenceCoursesCalculated);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = true
    // assign0WhenTheMarkIsNotEntered = true
    // $activateEliminationMarks = false
    // 1 1 0
    function calculateSequenceWeightingAverageIsCoeff1Assign1Activate0(array $markSequenceCoursesCalculated): array | float | null {
        foreach ($markSequenceCoursesCalculated as $markSequenceCourseCalculated) {
            if ($markSequenceCourseCalculated->getMark() === null) $markSequenceCourseCalculated->setMark(0);
        }

        $notNullMarkSequenceCoursesCalculated = $this->getNotNullMarks($markSequenceCoursesCalculated);

        // Maintenant si toutes les notes restantes sont nulles => moyenne nulle
        if (empty($notNullMarkSequenceCoursesCalculated)) {
            return 0;
        }

        return $this->getWeightingAverage($notNullMarkSequenceCoursesCalculated,true);
    }

    // $isCoefficientOfNullMarkConsiderInTheAverageCalculation = true
    // assign0WhenTheMarkIsNotEntered = true
    // $activateEliminationMarks = true
    // 1 1 1
    function calculateSequenceWeightingAverageIsCoeff1Assign1Activate1(array $markSequenceCoursesCalculated): array | float | null {
        foreach ($markSequenceCoursesCalculated as $markSequenceCourseCalculated) {
            if ($markSequenceCourseCalculated->getMark() === null){
                $markSequenceCourseCalculated->setMark(0);
            }
        }

        // On retire les notes eliminees
        $markSequenceCoursesCalculated = $this->getNotEliminatedMarks($markSequenceCoursesCalculated);
        if (empty($markSequenceCoursesCalculated)) return null;
        return $this->calculateSequenceWeightingAverageIsCoeff1Assign1Activate0($markSequenceCoursesCalculated);
    }
}