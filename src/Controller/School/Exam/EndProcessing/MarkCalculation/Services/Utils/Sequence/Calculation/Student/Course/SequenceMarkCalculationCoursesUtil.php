<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\Course;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Generation\Student\Course\SequenceMarkGenerationCourseUtil;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Mark;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;

// Classe contenant toutes les fonctions concernant le calcul des notes pour une sequence
class SequenceMarkCalculationCoursesUtil
{
// Configurations
//    private bool $isEliminationCourseActivated = false;


    public function __construct(
        // Configurations
//        private ?float $eliminateMark,
//        private bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation,

        // Utils

        // Utils
        private SequenceMarkGenerationCourseUtil  $sequenceMarkGenerationStudentCourseUtil,
        private SequenceMarkCalculationCourseUtil $sequenceMarkCalculationCourseUtil
    )
    {
//        $this->isEliminationCourseActivated = $this->sequenceMarkCalculationUtil->isEliminationCourseActivated();
    }

    // Getters & setters
    public function getSequenceMarkGenerationStudentCourseUtil(): SequenceMarkGenerationCourseUtil
    {
        return $this->sequenceMarkGenerationStudentCourseUtil;
    }

    public function setSequenceMarkGenerationStudentCourseUtil(SequenceMarkGenerationCourseUtil $sequenceMarkGenerationStudentCourseUtil): SequenceMarkCalculationCoursesUtil
    {
        $this->sequenceMarkGenerationStudentCourseUtil = $sequenceMarkGenerationStudentCourseUtil;
        return $this;
    }

    public function getSequenceMarkCalculationCourseUtil(): SequenceMarkCalculationCourseUtil
    {
        return $this->sequenceMarkCalculationCourseUtil;
    }

    public function setSequenceMarkCalculationCourseUtil(SequenceMarkCalculationCourseUtil $sequenceMarkCalculationCourseUtil): SequenceMarkCalculationCoursesUtil
    {
        $this->sequenceMarkCalculationCourseUtil = $sequenceMarkCalculationCourseUtil;
        return $this;
    }

    public function getCourses(StudentRegistration $student)
    {
        return $this->sequenceMarkCalculationCourseUtil->getCourses($student);
    }


    function getCourseMarks(StudentCourseRegistration $studentCourseRegistration) {
        $sequenceMarks = $this->sequenceMarkCalculationCourseUtil->getMarks($studentCourseRegistration);
        return $sequenceMarks;
    }

    // Calculer la note d'une sequence d'une matiere en considerant les type de notes
    /**
     * @param mixed $studentCourseRegistration
     * @return float|null
     */
    public function calculateSequenceCourseMark(StudentCourseRegistration $studentCourseRegistration): array
    {
        $sequenceMarks = $this->getCourseMarks($studentCourseRegistration);
        $marks = array_map(fn(Mark $sequenceMark)=>$sequenceMark->getMark(),$sequenceMarks);
        $mark = $this->sequenceMarkCalculationCourseUtil->calculateMark($sequenceMarks); //$marks);
        return ['mark'=>$mark,'sequenceMarks'=>$sequenceMarks];
    }

    // Calculer la note d'une sequence des matieres en considerant les type de notes
    function calculateSequenceCourseMarks(array $studentCourseRegistrations) : array
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

        $markSequenceCoursesCalculated = [];
        $moduleMarkSequenceCoursesCalculated = [];
        $notModuleMarkSequenceCoursesCalculated = [];

        /*if ($this->isEliminationActivated) {
            foreach ($studentCourseRegistrations as $studentCourseRegistration) {
                $markExists = $this->markRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $this->sequence]) !== null;
                if ($markExists) {
                    $markResult = $this->calculateSequenceCourseMark($studentCourseRegistration);
                    $mark = $markResult['mark'];
                    $isEliminated = true;
                    $isCourseValidated = false;
                    $classProgram = $studentCourseRegistration->getClassProgram();
//                    dd($markResult,$markDatas,$mark);
                    $sequenceMarks = $markResult['sequenceMarks'];
                    $validationBase = $classProgram->getValidationBase();

                    if ($mark) {
                        $isEliminated = $mark < $this->eliminateMark;
                        $isCourseValidated = !$isEliminated && $validationBase && $mark >= $validationBase;
                    }

                    $marksResult[] = ['studentCourseRegistration' => $studentCourseRegistration, 'mark' => $mark, 'base' => 20, 'eliminated' => $isEliminated, 'validated' => $isCourseValidated,'classProgram'=>$classProgram];
                    $coeff = $classProgram->getCoeff();
                    $markSequenceCourseCalculated = $this->sequenceMarkGenerationStudentCourseUtil->generateSequenceCourseCalculatedElimination($studentCourseRegistration,$mark,20,$isCourseValidated,$isEliminated,$sequenceMarks,$classProgram,$this->eliminateMark, $validationBase, $coeff);
                    $markSequenceCoursesCalculated[] = $markSequenceCourseCalculated;
                }
            }
        } else {*/
            foreach ($studentCourseRegistrations as $studentCourseRegistration) {
                $markExists = $this->sequenceMarkCalculationCourseUtil->markExists($studentCourseRegistration);
                if ($markExists) {
                    $markResult = $this->calculateSequenceCourseMark($studentCourseRegistration);
                    $mark = $markResult['mark'];
                    $isCourseValidated = false;
                    $classProgram = $studentCourseRegistration->getClassProgram();
                    $sequenceMarks = $markResult['sequenceMarks'];
                    $validationBase = $classProgram->getValidationBase();

                    if (isset($mark)) {
                        $isCourseValidated = !isset($validationBase) || $mark >= $validationBase;
                    }

                    $module = $studentCourseRegistration->getModule();


//                    $marksResult[] = ['studentCourseRegistration' => $studentCourseRegistration, 'mark' => $mark, 'base' => 20, 'eliminated' => false, 'validated' => $isCourseValidated, 'sequenceMarks' => $sequenceMarks,'classProgram'=>$classProgram];
                    $coeff = $classProgram->getCoeff();
                    $markSequenceCourseCalculated = $this->sequenceMarkGenerationStudentCourseUtil->generateSequenceCourseCalculated($studentCourseRegistration,$mark,$isCourseValidated,$sequenceMarks,$classProgram,null, $validationBase, $coeff);
                    if ($module) $moduleMarkSequenceCoursesCalculated[$module->getId()][] = $markSequenceCourseCalculated;
                    else $notModuleMarkSequenceCoursesCalculated[] = $markSequenceCourseCalculated;

                    $markSequenceCoursesCalculated[] = $markSequenceCourseCalculated;
                }
            }
//        }
        $marksResult = [
        // Toutes les notes
            'markSequenceCoursesCalculated'=>$markSequenceCoursesCalculated,

        // Notes dans les modules
            'moduleMarkSequenceCoursesCalculated'=>$moduleMarkSequenceCoursesCalculated,

        // Notes sans les modules
            'notModuleMarkSequenceCoursesCalculated'=>$notModuleMarkSequenceCoursesCalculated
        ];
        return $marksResult;
    }
}