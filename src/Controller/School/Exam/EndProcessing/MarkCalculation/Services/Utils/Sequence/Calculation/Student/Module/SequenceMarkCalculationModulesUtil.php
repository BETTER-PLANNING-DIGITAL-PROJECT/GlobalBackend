<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\Module;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Generation\Student\Module\SequenceMarkGenerationModuleUtil;
use App\Repository\School\Study\Configuration\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;

// Classe contenant toutes les fonctions concernant le calcul des notes pour une sequence
class SequenceMarkCalculationModulesUtil
{
    public function __construct(
        // Utils
        private SequenceMarkGenerationModuleUtil  $sequenceMarkGenerationStudentModuleUtil,
        private SequenceMarkCalculationModuleUtil $sequenceMarkCalculationModuleUtil
    )
    {
    }

    // Getters & setters
    public function getSequenceMarkGenerationStudentModuleUtil(): SequenceMarkGenerationModuleUtil
    {
        return $this->sequenceMarkGenerationStudentModuleUtil;
    }

    public function setSequenceMarkGenerationStudentModuleUtil(SequenceMarkGenerationModuleUtil $sequenceMarkGenerationStudentModuleUtil): SequenceMarkCalculationModulesUtil
    {
        $this->sequenceMarkGenerationStudentModuleUtil = $sequenceMarkGenerationStudentModuleUtil;
        return $this;
    }

    public function getSequenceMarkCalculationModuleUtil(): SequenceMarkCalculationModuleUtil
    {
        return $this->sequenceMarkCalculationModuleUtil;
    }

    public function setSequenceMarkCalculationModuleUtil(SequenceMarkCalculationModuleUtil $sequenceMarkCalculationModuleUtil): SequenceMarkCalculationModulesUtil
    {
        $this->sequenceMarkCalculationModuleUtil = $sequenceMarkCalculationModuleUtil;
        return $this;
    }


    // Calculer la note d'une sequence des modules
    function calculateSequenceModuleMarks(array $moduleMarkSequenceCoursesCalculated)
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
        $markSequenceModulesCalculated = [];
//        dd($moduleMarkSequenceCoursesCalculated);
        foreach ($moduleMarkSequenceCoursesCalculated as $moduleId => $markSequenceCoursesCalculated) {
//            dd($moduleId);
            $module = $this->sequenceMarkCalculationModuleUtil->getModule($moduleId);

            $markDatas = $this->sequenceMarkCalculationModuleUtil->calculateMark($markSequenceCoursesCalculated);
            $mark = null;
            $averageGpa = null;
            if (is_array($markDatas)) {
                $mark = $markDatas['mark'];
                $averageGpa = $markDatas['averageGpa'];
            }
            else if (is_float($markDatas)) {
                $mark = $markDatas;
            }

            $markSequenceModuleCalculatedArray = $this->sequenceMarkGenerationStudentModuleUtil->generateSequenceModuleCalculated($module, $mark, $averageGpa, $markSequenceCoursesCalculated);
            array_push($markSequenceModulesCalculated,...$markSequenceModuleCalculatedArray);
        }

        return  $markSequenceModulesCalculated;
    }
}