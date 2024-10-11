<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Generation\Student\Module;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Generation\Student\GeneralAverage\SequenceMarkGenerationGeneralAverageUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Generation\Student\SequenceMarkGenerationUtil;
use App\Entity\School\Exam\Configuration\MarkGrade;
use App\Entity\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculated;
use App\Entity\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculated;
use App\Entity\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculationRelation;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Study\Configuration\Module;
use App\Entity\School\Study\Program\ClassProgram;
use Doctrine\ORM\EntityManagerInterface;

// Classe contenant toutes les fonctions concernant la generation des notes de matiere pour une sequence
class SequenceMarkGenerationModuleUtil
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,

        // Attributs principaux
        private SequenceMarkGenerationUtil   $sequenceMarkGenerationUtil,
    )
    {

    }

    public function getSequenceMarkGenerationUtil(): SequenceMarkGenerationUtil
    {
        return $this->sequenceMarkGenerationUtil;
    }

    public function setSequenceMarkGenerationUtil(SequenceMarkGenerationUtil $sequenceMarkGenerationUtil): SequenceMarkGenerationModuleUtil
    {
        $this->sequenceMarkGenerationUtil = $sequenceMarkGenerationUtil;
        return $this;
    }

    // Generer la note d'un module d'une sequence
    function generateSequenceModuleCalculated(Module $module, ?float $mark, ?float $averageGpa,array $markSequenceCoursesCalculated): array
    {

        $markSequenceModuleCalculated = new MarkSequenceModuleCalculated();
        $this->sequenceMarkGenerationUtil->setMarkSequence($markSequenceModuleCalculated);
        $markSequenceModuleCalculated->setModule($module);
        $markSequenceModuleCalculated->setMark($mark);
        
        $markSequenceModuleCalculated->setAverageGpa($averageGpa);

        $this->sequenceMarkGenerationUtil->setMarkGrade($markSequenceModuleCalculated, $mark);
        // ,ClassProgram $classProgram,NoteType $noteType,Mark $sequenceMark,MarkSequenceCourseCalculated $markSequenceModuleCalculated

        $markSequenceModulesCalculatedArray = [];
        $totalCreditValidated = $totalCredit = $total = 0;
        foreach ($markSequenceCoursesCalculated as $markSequenceCourseCalculated) {
            $studentCourseRegistration = $markSequenceCourseCalculated->getStudentCourseRegistration();
            $classProgram = $markSequenceCourseCalculated->getClassProgram();
            $markUsed = $markSequenceCourseCalculated->getMark();
            $gradeUsed = $markSequenceCourseCalculated->getGrade();
            $gpaUsed = $gradeUsed?->getGpa();
            $isEliminated = $markSequenceCourseCalculated->getIsEliminated();
            $coeff = $classProgram->getCoeff();
            $validationBase = $classProgram->getValidationBase();
            $eliminateMark = $markSequenceCourseCalculated->getEliminateMark();
            $isCourseValidated = $markSequenceCourseCalculated->getIsCourseValidated();

            if ($isCourseValidated) $totalCreditValidated += $coeff;
            $totalCredit += $coeff;
            $total += $markSequenceCourseCalculated->getTotal();

            $markSequenceModulesCalculatedArray[] = $markSequenceCourseCalculated;
            $this->generateSequenceModuleCalculationRelation($module,$studentCourseRegistration, $classProgram, $markSequenceCourseCalculated, $markSequenceModuleCalculated, $mark,$markUsed,$averageGpa,$gpaUsed,$isEliminated,$gradeUsed,$coeff,$eliminateMark,$validationBase,$isCourseValidated);
        }
        $markSequenceModuleCalculated->setTotal($total);
        $markSequenceModuleCalculated->setTotalCredit($totalCredit);
        $markSequenceModuleCalculated->setTotalCreditValidated($totalCreditValidated);

        $markSequenceModulesCalculatedArray[] = $markSequenceModuleCalculated;
        $this->entityManager->flush();
        return $markSequenceModulesCalculatedArray;
    }

    // Generer la relation de calcul dans la table intermediaire
    function generateSequenceModuleCalculationRelation(Module $module, StudentCourseRegistration $studentCourseRegistration, ClassProgram $classProgram, MarkSequenceCourseCalculated $markSequenceCourseCalculated, MarkSequenceModuleCalculated $markSequenceModuleCalculated, ?float $mark, ?float $markUsed, ?float $averageGpaCalculated, ?float $gpaUsed, ?bool $isEliminated, ?MarkGrade $gradeUsed, ?float $coeff, ?float $eliminateMark, ?float $validationBase, bool $isCourseValidated)
    {
        $markSequenceModuleCalculationRelation = new MarkSequenceModuleCalculationRelation();
        $this->sequenceMarkGenerationUtil->setMarkSequence($markSequenceModuleCalculationRelation);
        $markSequenceModuleCalculationRelation->setModule($module);
        $markSequenceModuleCalculationRelation->setStudentCourseRegistration($studentCourseRegistration);
        $markSequenceModuleCalculationRelation->setClassProgram($classProgram);
        $markSequenceModuleCalculationRelation->setMarkCourseCalculated($markSequenceCourseCalculated);
        $markSequenceModuleCalculationRelation->setMarkModuleCalculated($markSequenceModuleCalculated);
        $markSequenceModuleCalculationRelation->setMarkCalculated($mark);
        $markSequenceModuleCalculationRelation->setMarkUsed($markUsed);
        $markSequenceModuleCalculationRelation->setAverageGPACalculated($averageGpaCalculated);
        $markSequenceModuleCalculationRelation->setGpaUsed($gpaUsed);
        $markSequenceModuleCalculationRelation->setCoeff($classProgram->getCoeff());
        $markSequenceModuleCalculationRelation->setIsEliminated($isEliminated);
        $markSequenceModuleCalculationRelation->setGradeUsed($gradeUsed);
        $markSequenceModuleCalculationRelation->setEliminateMark($eliminateMark);
        $markSequenceModuleCalculationRelation->setValidationBase($validationBase);
        $markSequenceModuleCalculationRelation->setCoeff($coeff);
        $markSequenceModuleCalculationRelation->setTotal($markSequenceCourseCalculated->getTotal());
        $markSequenceModuleCalculationRelation->setIsCourseValidated($isCourseValidated);
    }
}