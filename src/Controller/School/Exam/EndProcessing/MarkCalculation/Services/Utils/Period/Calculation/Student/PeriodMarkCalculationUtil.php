<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Calculation\Student;

// Classe contenant toutes les fonctions concernant le calcul des notes pour une sequence
use App\Entity\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculated;

// Il serait preferable de creer un sequence mark calculation pour chaque type (Matiere,Module,Moyenne generale)
class PeriodMarkCalculationUtil
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
}