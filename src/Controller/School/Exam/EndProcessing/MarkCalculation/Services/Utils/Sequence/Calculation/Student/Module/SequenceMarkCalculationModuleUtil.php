<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\Module;

// Classe contenant toutes les fonctions concernant le calcul des notes pour une sequence
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\SequenceMarkCalculationUtil;
use App\Repository\School\Study\Configuration\ModuleRepository;

// Il serait preferable de creer un sequence mark calculation pour chaque type (Matiere,Module,Moyenne generale)
class SequenceMarkCalculationModuleUtil extends SequenceMarkCalculationUtil
{

    public function __construct(
        // Configurations
        // Calc des notes d'une matiere
        protected bool     $assign0WhenTheMarkIsNotEntered,
        protected bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation,

        // Repository
        private  readonly ?ModuleRepository $moduleRepository = null
)
    {
        parent::__construct($this->assign0WhenTheMarkIsNotEntered,$this->isCoefficientOfNullMarkConsiderInTheAverageCalculation);

        $this->calculateMethod = 'calculateSequenceWeightingAverageIsCoeff'.(int)($this->isCoefficientOfNullMarkConsiderInTheAverageCalculation).'Assign'.(int)($this->assign0WhenTheMarkIsNotEntered).'Activate'.(0); // calculateSequenceWeightingAverageIsCoeff0Assign0Activate0
    }

    public function getModule(int $id)
    {
        return $this->moduleRepository->find($id);
    }
}