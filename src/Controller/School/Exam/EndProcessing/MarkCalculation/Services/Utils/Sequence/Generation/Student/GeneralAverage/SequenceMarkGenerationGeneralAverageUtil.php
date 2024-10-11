<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Generation\Student\GeneralAverage;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Generation\Student\SequenceMarkGenerationUtil;
use App\Entity\School\Exam\Configuration\MarkGrade;
use App\Entity\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculated;
use App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculated;
use App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculationRelation;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Study\Configuration\Module;
use App\Entity\School\Study\Program\ClassProgram;
use Doctrine\ORM\EntityManagerInterface;

// Classe contenant toutes les fonctions concernant la generation des notes de matiere pour une sequence
class SequenceMarkGenerationGeneralAverageUtil
{
    public function __construct(
        // Attributs principaux
        private readonly EntityManagerInterface                 $entityManager,

        // Attributs principaux
        private SequenceMarkGenerationUtil $sequenceMarkGenerationUtil,
    )
    {

    }

    public function getSequenceMarkGenerationUtil(): SequenceMarkGenerationUtil
    {
        return $this->sequenceMarkGenerationUtil;
    }

    public function setSequenceMarkGenerationUtil(SequenceMarkGenerationUtil $sequenceMarkGenerationUtil): SequenceMarkGenerationGeneralAverageUtil
    {
        $this->sequenceMarkGenerationUtil = $sequenceMarkGenerationUtil;
        return $this;
    }

    // Generer la moyenne generale d'une sequence d'une sequence
    function generateSequenceGeneralAverageCalculated(?float $average, ?float $averageGpa,int $numberOfAttendedCourses,int $numberOfComposedCourses,float $totalOfCreditsAttended,float $totalOfCreditsComposed,float $percentageSubjectNumber,
            float $percentageTotalCoefficient,bool $isClassed,array $markSequenceCoursesCalculated): MarkSequenceGeneralAverageCalculated
    {
        $totalCredit = 0;
        $totalCreditValidated = 0;
        $total = 0;

        $markSequenceGeneralAverageCalculated = new MarkSequenceGeneralAverageCalculated();
        $this->sequenceMarkGenerationUtil->setMarkSequence($markSequenceGeneralAverageCalculated);
        $markSequenceGeneralAverageCalculated->setAverage($average);
        
        $markSequenceGeneralAverageCalculated->setAverageGpa($averageGpa);
        $markSequenceGeneralAverageCalculated->setNumberOfAttendedCourses($numberOfAttendedCourses);
        $markSequenceGeneralAverageCalculated->setNumberOfComposedCourses($numberOfComposedCourses);
        $markSequenceGeneralAverageCalculated->setTotalOfCreditsAttended($totalOfCreditsAttended);
        $markSequenceGeneralAverageCalculated->setTotalOfCreditsComposed($totalOfCreditsComposed);
        $markSequenceGeneralAverageCalculated->setPercentageSubjectNumber($percentageSubjectNumber);
        $markSequenceGeneralAverageCalculated->setPercentageTotalCoefficient($percentageTotalCoefficient);
        $markSequenceGeneralAverageCalculated->setIsClassed($isClassed);
//        $markSequenceGeneralAverageCalculated->setIsEliminated(!boolval($average));

        $this->sequenceMarkGenerationUtil->setMarkGrade($markSequenceGeneralAverageCalculated, $average);
        // ,ClassProgram $classProgram,NoteType $noteType,Mark $sequenceMark,MarkSequenceCourseCalculated $markSequenceGeneralAverageCalculated
        foreach ($markSequenceCoursesCalculated as $markSequenceCourseCalculated) {
            $classProgram = $markSequenceCourseCalculated->getClassProgram();
            $credit = $classProgram->getCoeff();

            if ($markSequenceCourseCalculated->getIsCourseValidated()) $totalCreditValidated += $credit;
            $totalCredit += $credit;
            $total += $markSequenceCourseCalculated->getTotal();

            $studentCourseRegistration = $markSequenceCourseCalculated->getStudentCourseRegistration();
            $markUsed = $markSequenceCourseCalculated->getMark();
            $gradeUsed = $markSequenceCourseCalculated->getGrade();
            $gpaUsed = $gradeUsed?->getGpa();
            $isEliminated = $markSequenceCourseCalculated->getIsEliminated();
            $coeff = $classProgram->getCoeff();
            $validationBase = $classProgram->getValidationBase();
            $eliminateMark = $markSequenceCourseCalculated->getEliminateMark();
            $isCourseValidated = $markSequenceCourseCalculated->getIsCourseValidated();
            $module = $classProgram->getModule();

            $this->generateSequenceGeneralAverageCalculationRelation($module,$studentCourseRegistration, $classProgram, $markSequenceCourseCalculated, $markSequenceGeneralAverageCalculated, $average,$markUsed,$averageGpa,$gpaUsed,$isEliminated,$gradeUsed,$coeff,$eliminateMark,$validationBase,$isCourseValidated);
        }
        $markSequenceGeneralAverageCalculated->setTotal($total);
        $markSequenceGeneralAverageCalculated->setTotalCredit($totalCredit);
        $markSequenceGeneralAverageCalculated->setTotalCreditValidated($totalCreditValidated);
        $this->entityManager->flush();
        return $markSequenceGeneralAverageCalculated;
    }

    // Generer la relation de calcul dans la table intermediaire
    function generateSequenceGeneralAverageCalculationRelation(?Module $module, StudentCourseRegistration $studentCourseRegistration, ClassProgram $classProgram, MarkSequenceCourseCalculated $markSequenceCourseCalculated, MarkSequenceGeneralAverageCalculated $markSequenceGeneralAverageCalculated, ?float $average, ?float $markUsed, ?float $averageGpaCalculated, ?float $gpaUsed, ?bool $isEliminated, ?MarkGrade $gradeUsed, ?float $coeff, ?float $eliminateMark, ?float $validationBase, bool $isCourseValidated)
    {
        $markSequenceGeneralAverageCalculationRelation = new MarkSequenceGeneralAverageCalculationRelation();
        $this->sequenceMarkGenerationUtil->setMarkSequence($markSequenceGeneralAverageCalculationRelation);
        $markSequenceGeneralAverageCalculationRelation->setModule($module);
        $markSequenceGeneralAverageCalculationRelation->setStudentCourseRegistration($studentCourseRegistration);
        $markSequenceGeneralAverageCalculationRelation->setClassProgram($classProgram);
        $markSequenceGeneralAverageCalculationRelation->setMarkCourseCalculated($markSequenceCourseCalculated);
        $markSequenceGeneralAverageCalculationRelation->setMarkSequenceGeneralAverageCalculated($markSequenceGeneralAverageCalculated);
        $markSequenceGeneralAverageCalculationRelation->setAverageCalculated($average);
        $markSequenceGeneralAverageCalculationRelation->setMarkUsed($markUsed);
        $markSequenceGeneralAverageCalculationRelation->setAverageGPACalculated($averageGpaCalculated);
        $markSequenceGeneralAverageCalculationRelation->setGpaUsed($gpaUsed);
        $markSequenceGeneralAverageCalculationRelation->setIsEliminated($isEliminated);
        $markSequenceGeneralAverageCalculationRelation->setGradeUsed($gradeUsed);
        $markSequenceGeneralAverageCalculationRelation->setEliminateMark($eliminateMark);
        $markSequenceGeneralAverageCalculationRelation->setValidationBase($validationBase);
        $markSequenceGeneralAverageCalculationRelation->setCoeff($coeff);
        $markSequenceGeneralAverageCalculationRelation->setTotal($markSequenceCourseCalculated->getTotal());
        $markSequenceGeneralAverageCalculationRelation->setIsCourseValidated($isCourseValidated);
    }
}