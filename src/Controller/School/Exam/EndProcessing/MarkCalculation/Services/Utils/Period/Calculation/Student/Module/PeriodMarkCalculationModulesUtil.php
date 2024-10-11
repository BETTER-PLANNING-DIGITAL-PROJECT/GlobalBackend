<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Calculation\Student\Module;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Generation\Student\Module\PeriodMarkGenerationModuleUtil;
use App\Repository\School\Study\Configuration\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;

// Classe contenant toutes les fonctions concernant le calcul des notes pour une sequence
class PeriodMarkCalculationModulesUtil
{

    private bool $isModulable;
    private bool $isEliminable;
    private string $calculatePeriodModuleMarksMethod;

    public function __construct(
        private ?float                                             $validationBase,
        private ?float                                             $groupEliminateAverage,
        // Utils
        private PeriodMarkGenerationModuleUtil                     $periodMarkGenerationModuleUtil,
        private PeriodMarkCalculationModuleUtil $periodMarkCalculationModuleUtil
    )
    {
        $this->isModulable = $this->periodMarkCalculationModuleUtil->isModulable();
        $this->isEliminable = $this->periodMarkCalculationModuleUtil->isEliminable();
        $this->calculatePeriodModuleMarksMethod = 'calculatePeriodModuleMarksIsElim'.(int) $this->isEliminable.'IsMod'.(int) $this->isModulable;
    }

    // Getters & setters
    public function getPeriodMarkGenerationModuleUtil(): PeriodMarkGenerationModuleUtil
    {
        return $this->periodMarkGenerationModuleUtil;
    }

    public function setPeriodMarkGenerationModuleUtil(PeriodMarkGenerationModuleUtil $periodMarkGenerationModuleUtil): PeriodMarkCalculationModulesUtil
    {
        $this->periodMarkGenerationModuleUtil = $periodMarkGenerationModuleUtil;
        return $this;
    }

    public function getPeriodMarkCalculationModuleUtil(): PeriodMarkCalculationModuleUtil
    {
        return $this->periodMarkCalculationModuleUtil;
    }

    public function setPeriodMarkCalculationModuleUtil(PeriodMarkCalculationModuleUtil $periodMarkCalculationModuleUtil): PeriodMarkCalculationModulesUtil
    {
        $this->periodMarkCalculationModuleUtil = $periodMarkCalculationModuleUtil;
        return $this;
    }

    function calculatePeriodModuleMarks(array $moduleMarkPeriodCoursesCalculated){
        return $this->{$this->calculatePeriodModuleMarksMethod}($moduleMarkPeriodCoursesCalculated);
    }

    // Calculer la note d'une sequence des modules
    // isEliminable = false
    // isModulable = false
    // 0 0
    function calculatePeriodModuleMarksIsElim0IsMod0(array $moduleMarkPeriodCoursesCalculated)
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
        $markPeriodModulesCalculated = [];
//        dd($moduleMarkSequenceCoursesCalculated);
        foreach ($moduleMarkPeriodCoursesCalculated as $moduleId => $markPeriodCoursesCalculated) {
//            dd($moduleId);
            $module = $this->periodMarkCalculationModuleUtil->getModule($moduleId);

            $markDatas = $this->periodMarkCalculationModuleUtil->calculateMark($markPeriodCoursesCalculated);
            $mark = null;
            $averageGpa = null;
            if (is_array($markDatas)) {
                $mark = $markDatas['mark'];
                $averageGpa = $markDatas['averageGpa'];
            }
            else if (is_float($markDatas)) {
                $mark = $markDatas;
            }

            $markSequenceModuleCalculatedArray = $this->periodMarkGenerationModuleUtil->generatePeriodModuleCalculated($module, $mark, $averageGpa, $this->validationBase,false,false, $markPeriodCoursesCalculated);
            array_push($markPeriodModulesCalculated,...$markSequenceModuleCalculatedArray);
        }

        return  $markPeriodModulesCalculated;
    }

    // isEliminable = false
    // isModulable = true
    // 0 1
    function calculatePeriodModuleMarksIsElim0IsMod1(array $moduleMarkPeriodCoursesCalculated)
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
        $markPeriodModulesCalculated = [];
//        dd($moduleMarkSequenceCoursesCalculated);
        foreach ($moduleMarkPeriodCoursesCalculated as $moduleId => $markPeriodCoursesCalculated) {
//            dd($moduleId);
            $module = $this->periodMarkCalculationModuleUtil->getModule($moduleId);

            $markDatas = $this->periodMarkCalculationModuleUtil->calculateMark($markPeriodCoursesCalculated);
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
            $markSequenceModuleCalculatedArray = $this->periodMarkGenerationModuleUtil->generatePeriodModuleCalculated($module, $mark, $averageGpa, $this->validationBase,$isModulated,false, $markPeriodCoursesCalculated);
            array_push($markPeriodModulesCalculated,...$markSequenceModuleCalculatedArray);
        }

        return  $markPeriodModulesCalculated;
    }

    // isEliminable = true
    // isModulable = false
    // 1 0
    function calculatePeriodModuleMarksIsElim1IsMod0(array $moduleMarkPeriodCoursesCalculated)
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
        $markPeriodModulesCalculated = [];
//        dd($moduleMarkSequenceCoursesCalculated);
        foreach ($moduleMarkPeriodCoursesCalculated as $moduleId => $markPeriodCoursesCalculated) {
//            dd($moduleId);
            $module = $this->periodMarkCalculationModuleUtil->getModule($moduleId);

            $markDatas = $this->periodMarkCalculationModuleUtil->calculateMark($markPeriodCoursesCalculated);
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
            $markSequenceModuleCalculatedArray = $this->periodMarkGenerationModuleUtil->generatePeriodModuleCalculated($module, $mark, $averageGpa, $this->validationBase,false,$isEliminated, $markPeriodCoursesCalculated);
            array_push($markPeriodModulesCalculated,...$markSequenceModuleCalculatedArray);
        }

        return  $markPeriodModulesCalculated;
    }

    // isEliminable = true
    // isModulable = true
    // 1 1
    function calculatePeriodModuleMarksIsElim1IsMod1(array $moduleMarkPeriodCoursesCalculated)
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
        $markPeriodModulesCalculated = [];
//        dd($moduleMarkSequenceCoursesCalculated);
        foreach ($moduleMarkPeriodCoursesCalculated as $moduleId => $markPeriodCoursesCalculated) {
//            dd($moduleId);
            $module = $this->periodMarkCalculationModuleUtil->getModule($moduleId);

            $markDatas = $this->periodMarkCalculationModuleUtil->calculateMark($markPeriodCoursesCalculated);
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
            $markSequenceModuleCalculatedArray = $this->periodMarkGenerationModuleUtil->generatePeriodModuleCalculated($module, $mark, $averageGpa, $this->validationBase,$isModulated,$isEliminated, $markPeriodCoursesCalculated);
            array_push($markPeriodModulesCalculated,...$markSequenceModuleCalculatedArray);
        }

        return  $markPeriodModulesCalculated;
    }
}