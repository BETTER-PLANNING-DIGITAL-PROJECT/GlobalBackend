<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Module\PrimaryAndSecondary;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Module\PrimaryAndSecondary\AnnualMarkCalculationModulePrimaryAndSecondaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Generation\Student\Module\AnnualMarkGenerationModuleUtil;
use App\Repository\School\Study\Configuration\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;

// Classe contenant toutes les fonctions concernant le calcul des notes pour une sequence
class AnnualMarkCalculationModulesPrimaryAndSecondaryUtil
{

    private bool $isModulable;
    private bool $isEliminable;
    private string $calculateAnnualModuleMarksMethod;

    public function __construct(
        private ?float                          $validationBase,
        private ?float                          $groupEliminateAverage,
        // Utils
        private AnnualMarkGenerationModuleUtil  $annualMarkGenerationModuleUtil,
        private AnnualMarkCalculationModulePrimaryAndSecondaryUtil $annualMarkCalculationModuleUtil
    )
    {
        $this->isModulable = $this->annualMarkCalculationModuleUtil->isModulable();
        $this->isEliminable = $this->annualMarkCalculationModuleUtil->isEliminable();
        $this->calculateAnnualModuleMarksMethod = 'calculateAnnualModuleMarksIsElim'.(int) $this->isEliminable.'IsMod'.(int) $this->isModulable;
    }

    // Getters & setters
    public function getAnnualMarkGenerationModuleUtil(): AnnualMarkGenerationModuleUtil
    {
        return $this->annualMarkGenerationModuleUtil;
    }

    public function setAnnualMarkGenerationModuleUtil(AnnualMarkGenerationModuleUtil $annualMarkGenerationModuleUtil): AnnualMarkCalculationModulesPrimaryAndSecondaryUtil
    {
        $this->annualMarkGenerationModuleUtil = $annualMarkGenerationModuleUtil;
        return $this;
    }

    public function getAnnualMarkCalculationModuleUtil(): AnnualMarkCalculationModulePrimaryAndSecondaryUtil
    {
        return $this->annualMarkCalculationModuleUtil;
    }

    public function setAnnualMarkCalculationModuleUtil(AnnualMarkCalculationModulePrimaryAndSecondaryUtil $annualMarkCalculationModuleUtil): AnnualMarkCalculationModulesPrimaryAndSecondaryUtil
    {
        $this->annualMarkCalculationModuleUtil = $annualMarkCalculationModuleUtil;
        return $this;
    }

    function calculateAnnualModuleMarks(array $moduleMarkAnnualCoursesCalculated){
        return $this->{$this->calculateAnnualModuleMarksMethod}($moduleMarkAnnualCoursesCalculated);
    }

    // Calculer la note d'une sequence des modules
    // isEliminable = false
    // isModulable = false
    // 0 0
    function calculateAnnualModuleMarksIsElim0IsMod0(array $moduleMarkAnnualCoursesCalculated)
    {
        /*$markCreditFn = $this->activateWeightingsPerAssignment ? function (StudentCourseRegistration $studentCourseRegistration) {
            $sequenceMarks = $this->markRepository->findBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $this->sequence]);
            $marks = array_map(fn(Mark $sequenceMark) => ['mark' => $sequenceMark->getMark(), 'credit' => $sequenceMark->getNoteType() ? $sequenceMark->getNoteType()->getWeighting() : 1], $sequenceMarks);
            return $marks;
        } : function (StudentCourseRegistration $studentCourseRegistration) {
            $sequenceMarks = $this->markRepository->findBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $this->sequence]);
            $marks = array_map(fn(Mark $sequenceMark) => ['mark' => $sequenceMark->getMark(), 'credit' => 1], $sequenceMarks);
            return $marks;
        };*/

        // Calcul de la note du module et enregistrements
        $markAnnualModulesCalculated = [];
//        dd($moduleMarkSequenceCoursesCalculated);
        foreach ($moduleMarkAnnualCoursesCalculated as $moduleId => $markAnnualCoursesCalculated) {
//            dd($moduleId);
            $module = $this->annualMarkCalculationModuleUtil->getModule($moduleId);

            $markDatas = $this->annualMarkCalculationModuleUtil->calculateMark($markAnnualCoursesCalculated);
            $mark = null;
            $averageGpa = null;
            if (is_array($markDatas)) {
                $mark = $markDatas['mark'];
                $averageGpa = $markDatas['averageGpa'];
            }
            else if (is_float($markDatas)) {
                $mark = $markDatas;
            }

            $markSequenceModuleCalculatedArray = $this->annualMarkGenerationModuleUtil->generateAnnualModuleCalculated($module, $mark, $averageGpa, $this->validationBase,false,false, $markAnnualCoursesCalculated);
            array_push($markAnnualModulesCalculated,...$markSequenceModuleCalculatedArray);
        }

        return  $markAnnualModulesCalculated;
    }

    // isEliminable = false
    // isModulable = true
    // 0 1
    function calculateAnnualModuleMarksIsElim0IsMod1(array $moduleMarkAnnualCoursesCalculated)
    {
        /*$markCreditFn = $this->activateWeightingsPerAssignment ? function (StudentCourseRegistration $studentCourseRegistration) {
            $sequenceMarks = $this->markRepository->findBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $this->sequence]);
            $marks = array_map(fn(Mark $sequenceMark) => ['mark' => $sequenceMark->getMark(), 'credit' => $sequenceMark->getNoteType() ? $sequenceMark->getNoteType()->getWeighting() : 1], $sequenceMarks);
            return $marks;
        } : function (StudentCourseRegistration $studentCourseRegistration) {
            $sequenceMarks = $this->markRepository->findBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $this->sequence]);
            $marks = array_map(fn(Mark $sequenceMark) => ['mark' => $sequenceMark->getMark(), 'credit' => 1], $sequenceMarks);
            return $marks;
        };*/

        // Calcul de la note du module et enregistrements
        $markAnnualModulesCalculated = [];
//        dd($moduleMarkSequenceCoursesCalculated);
        foreach ($moduleMarkAnnualCoursesCalculated as $moduleId => $markAnnualCoursesCalculated) {
//            dd($moduleId);
            $module = $this->annualMarkCalculationModuleUtil->getModule($moduleId);

            $markDatas = $this->annualMarkCalculationModuleUtil->calculateMark($markAnnualCoursesCalculated);
            $mark = null;
            $averageGpa = null;
            if (is_array($markDatas)) {
                $mark = $markDatas['mark'];
                $averageGpa = $markDatas['averageGpa'];
            }
            else if (is_float($markDatas)) {
                $mark = $markDatas;
            }
            $isModulated = isset($mark) && $mark >= $this->validationBase;
            $markSequenceModuleCalculatedArray = $this->annualMarkGenerationModuleUtil->generateAnnualModuleCalculated($module, $mark, $averageGpa, $this->validationBase,$isModulated,false, $markAnnualCoursesCalculated);
            array_push($markAnnualModulesCalculated,...$markSequenceModuleCalculatedArray);
        }

        return  $markAnnualModulesCalculated;
    }

    // isEliminable = true
    // isModulable = false
    // 1 0
    function calculateAnnualModuleMarksIsElim1IsMod0(array $moduleMarkAnnualCoursesCalculated)
    {
        /*$markCreditFn = $this->activateWeightingsPerAssignment ? function (StudentCourseRegistration $studentCourseRegistration) {
            $sequenceMarks = $this->markRepository->findBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $this->sequence]);
            $marks = array_map(fn(Mark $sequenceMark) => ['mark' => $sequenceMark->getMark(), 'credit' => $sequenceMark->getNoteType() ? $sequenceMark->getNoteType()->getWeighting() : 1], $sequenceMarks);
            return $marks;
        } : function (StudentCourseRegistration $studentCourseRegistration) {
            $sequenceMarks = $this->markRepository->findBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $this->sequence]);
            $marks = array_map(fn(Mark $sequenceMark) => ['mark' => $sequenceMark->getMark(), 'credit' => 1], $sequenceMarks);
            return $marks;
        };*/

        // Calcul de la note du module et enregistrements
        $markAnnualModulesCalculated = [];
//        dd($moduleMarkSequenceCoursesCalculated);
        foreach ($moduleMarkAnnualCoursesCalculated as $moduleId => $markAnnualCoursesCalculated) {
//            dd($moduleId);
            $module = $this->annualMarkCalculationModuleUtil->getModule($moduleId);

            $markDatas = $this->annualMarkCalculationModuleUtil->calculateMark($markAnnualCoursesCalculated);
            $mark = null;
            $averageGpa = null;
            if (is_array($markDatas)) {
                $mark = $markDatas['mark'];
                $averageGpa = $markDatas['averageGpa'];
            }
            else if (is_float($markDatas)) {
                $mark = $markDatas;
            }

            $isEliminated = !isset($mark) || ($mark < $this->groupEliminateAverage);
            $markSequenceModuleCalculatedArray = $this->annualMarkGenerationModuleUtil->generateAnnualModuleCalculated($module, $mark, $averageGpa, $this->validationBase,false,$isEliminated, $markAnnualCoursesCalculated);
            array_push($markAnnualModulesCalculated,...$markSequenceModuleCalculatedArray);
        }

        return  $markAnnualModulesCalculated;
    }

    // isEliminable = true
    // isModulable = true
    // 1 1
    function calculateAnnualModuleMarksIsElim1IsMod1(array $moduleMarkAnnualCoursesCalculated)
    {
        /*$markCreditFn = $this->activateWeightingsPerAssignment ? function (StudentCourseRegistration $studentCourseRegistration) {
            $sequenceMarks = $this->markRepository->findBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $this->sequence]);
            $marks = array_map(fn(Mark $sequenceMark) => ['mark' => $sequenceMark->getMark(), 'credit' => $sequenceMark->getNoteType() ? $sequenceMark->getNoteType()->getWeighting() : 1], $sequenceMarks);
            return $marks;
        } : function (StudentCourseRegistration $studentCourseRegistration) {
            $sequenceMarks = $this->markRepository->findBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $this->sequence]);
            $marks = array_map(fn(Mark $sequenceMark) => ['mark' => $sequenceMark->getMark(), 'credit' => 1], $sequenceMarks);
            return $marks;
        };*/

        // Calcul de la note du module et enregistrements
        $markAnnualModulesCalculated = [];
//        dd($moduleMarkSequenceCoursesCalculated);
        foreach ($moduleMarkAnnualCoursesCalculated as $moduleId => $markAnnualCoursesCalculated) {
//            dd($moduleId);
            $module = $this->annualMarkCalculationModuleUtil->getModule($moduleId);

            $markDatas = $this->annualMarkCalculationModuleUtil->calculateMark($markAnnualCoursesCalculated);
            $mark = null;
            $averageGpa = null;
            if (is_array($markDatas)) {
                $mark = $markDatas['mark'];
                $averageGpa = $markDatas['averageGpa'];
            }
            else if (is_float($markDatas)) {
                $mark = $markDatas;
            }

            $isEliminated = !isset($mark) || ($mark < $this->groupEliminateAverage);
            $isModulated = !$isEliminated && $mark >= $this->validationBase;
            $markSequenceModuleCalculatedArray = $this->annualMarkGenerationModuleUtil->generateAnnualModuleCalculated($module, $mark, $averageGpa, $this->validationBase,$isModulated,$isEliminated, $markAnnualCoursesCalculated);
            array_push($markAnnualModulesCalculated,...$markSequenceModuleCalculatedArray);
        }

        return  $markAnnualModulesCalculated;
    }
}