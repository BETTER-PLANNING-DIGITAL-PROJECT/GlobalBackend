<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Generation\Student\Module;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Generation\Student\PeriodMarkGenerationUtil;
use App\Entity\School\Exam\Configuration\MarkGrade;
use App\Entity\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculated;
use App\Entity\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculated;
use App\Entity\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculationRelation;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Study\Configuration\Module;
use App\Entity\School\Study\Program\ClassProgram;
use Doctrine\ORM\EntityManagerInterface;

// Classe contenant toutes les fonctions concernant la generation des notes de matiere pour une sequence
class PeriodMarkGenerationModuleUtil
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,

        // Attributs principaux
        private PeriodMarkGenerationUtil        $periodMarkGenerationUtil,
    )
    {

    }

    public function getPeriodMarkGenerationUtil(): PeriodMarkGenerationUtil
    {
        return $this->periodMarkGenerationUtil;
    }

    public function setPeriodMarkGenerationUtil(PeriodMarkGenerationUtil $periodMarkGenerationUtil): PeriodMarkGenerationModuleUtil
    {
        $this->periodMarkGenerationUtil = $periodMarkGenerationUtil;
        return $this;
    }


    // Generer la note d'un module d'une sequence
    function generatePeriodModuleCalculated(Module $module, ?float $mark, ?float $averageGpa, ?float $validationBase, bool $isModulated, bool $isEliminated, array $markPeriodCoursesCalculated): array
    {

        $markPeriodModuleCalculated = new MarkPeriodModuleCalculated();
        $this->periodMarkGenerationUtil->setMarkPeriod($markPeriodModuleCalculated);
        $markPeriodModuleCalculated->setModule($module);
        $markPeriodModuleCalculated->setMark($mark);

        $markPeriodModuleCalculated->setAverageGpa($averageGpa);
        $markPeriodModuleCalculated->setIsEliminated($isEliminated);
        $this->periodMarkGenerationUtil->setMarkGrade($markPeriodModuleCalculated, $mark);
        // ,ClassProgram $classProgram,NoteType $noteType,Mark $sequenceMark,MarkSequenceCourseCalculated $markPeriodCourseCalculated

        $markPeriodModulesCalculatedArray = [];
        $totalCreditValidated = $totalCredit = $totalCreditConsidered = $totalMarks = 0;
        if (!$isModulated) {
            if ($isEliminated) {
                foreach ($markPeriodCoursesCalculated as $markPeriodCourseCalculated) {
                    $studentCourseRegistration = $markPeriodCourseCalculated->getStudentCourseRegistration();
                    $classProgram = $markPeriodCourseCalculated->getClassProgram();
                    $markUsed = $markPeriodCourseCalculated->getMark();
                    $gradeUsed = $markPeriodCourseCalculated->getGrade();
                    $totalCourseMarksUsed = $markPeriodCourseCalculated->getTotal();
                    $gpaUsed = $gradeUsed?->getGpa();
                    $coeff = $classProgram->getCoeff();
                    $isCourseValidated = $markPeriodCourseCalculated->getIsCourseValidated();

                    if ($isCourseValidated === 'v') {
                        $totalCreditValidated += $coeff;
                    }
                    $totalCredit += $coeff;
                    $totalMarks += $totalCourseMarksUsed;
                    $markPeriodCourseCalculated->setIsModuleEliminated(true);
                    $markPeriodModulesCalculatedArray[] = $markPeriodCourseCalculated;
                    $this->generatePeriodModuleCalculationRelation($module, $studentCourseRegistration, $classProgram, $markPeriodCourseCalculated, $markPeriodModuleCalculated, $mark, $markUsed, $averageGpa, $gpaUsed, $gradeUsed, $coeff, $validationBase, $isCourseValidated);
                }
            } else {
                foreach ($markPeriodCoursesCalculated as $markPeriodCourseCalculated) {
                    $studentCourseRegistration = $markPeriodCourseCalculated->getStudentCourseRegistration();
                    $classProgram = $markPeriodCourseCalculated->getClassProgram();
                    $markUsed = $markPeriodCourseCalculated->getMark();
                    $gradeUsed = $markPeriodCourseCalculated->getGrade();
                    $totalCourseMarksUsed = $markPeriodCourseCalculated->getTotal();
                    $gpaUsed = $gradeUsed?->getGpa();
                    $coeff = $classProgram->getCoeff();
                    $isCourseValidated = $markPeriodCourseCalculated->getIsCourseValidated();

                    if ($isCourseValidated === 'v') {
                        $totalCreditValidated += $coeff;
                    }

                    $totalCredit += $coeff;
                    $totalMarks += $totalCourseMarksUsed;
                    $markPeriodCourseCalculated->setIsModuleEliminated(false);
                    $markPeriodModulesCalculatedArray[] = $markPeriodCourseCalculated;
                    $this->generatePeriodModuleCalculationRelation($module, $studentCourseRegistration, $classProgram, $markPeriodCourseCalculated, $markPeriodModuleCalculated, $mark, $markUsed, $averageGpa, $gpaUsed, $gradeUsed, $coeff, $validationBase, $isCourseValidated);
                }
                $totalCreditConsidered = $totalCreditValidated;
            }
        } else {
            foreach ($markPeriodCoursesCalculated as $markPeriodCourseCalculated) {
                $studentCourseRegistration = $markPeriodCourseCalculated->getStudentCourseRegistration();
                $classProgram = $markPeriodCourseCalculated->getClassProgram();
                $markUsed = $markPeriodCourseCalculated->getMark();
                $gradeUsed = $markPeriodCourseCalculated->getGrade();
                $totalCourseMarksUsed = $markPeriodCourseCalculated->getTotal();
                $gpaUsed = $gradeUsed?->getGpa();
                $coeff = $classProgram->getCoeff();
                $isCourseValidated = $markPeriodCourseCalculated->getIsCourseValidated();

                if ($isCourseValidated === 'nv') {
                    $isCourseValidated = 'm';
                    $markPeriodCourseCalculated->setIsCourseValidated($isCourseValidated);
                } else {
                    $totalCreditValidated += $coeff;
                }
                $totalCredit += $coeff;
                $totalMarks += $totalCourseMarksUsed;

                $markPeriodModulesCalculatedArray[] = $markPeriodCourseCalculated;
                $this->generatePeriodModuleCalculationRelation($module, $studentCourseRegistration, $classProgram, $markPeriodCourseCalculated, $markPeriodModuleCalculated, $mark, $markUsed, $averageGpa, $gpaUsed, $gradeUsed, $coeff, $validationBase, $isCourseValidated);
            }
            $totalCreditConsidered = $totalCredit;
        }
        $markPeriodModuleCalculated->setTotalCredit($totalCredit);
        $markPeriodModuleCalculated->setTotal($totalMarks);
        $markPeriodModuleCalculated->setTotalCreditValidated($totalCreditValidated);
        $markPeriodModuleCalculated->setTotalCreditConsidered($totalCreditConsidered);

        $markPeriodModulesCalculatedArray[] = $markPeriodModuleCalculated;
        $this->entityManager->flush();
        return $markPeriodModulesCalculatedArray;
    }

    // Generer la relation de calcul dans la table intermediaire
    function generatePeriodModuleCalculationRelation(Module $module, StudentCourseRegistration $studentCourseRegistration, ClassProgram $classProgram, MarkPeriodCourseCalculated $markPeriodCourseCalculated, MarkPeriodModuleCalculated $markPeriodModuleCalculated, ?float $mark, ?float $markUsed, ?float $averageGpaCalculated, ?float $gpaUsed, ?MarkGrade $gradeUsed, ?float $coeff, ?float $validationBase, string $isCourseValidated)
    {
        $markPeriodModuleCalculationRelation = new MarkPeriodModuleCalculationRelation();
        $this->periodMarkGenerationUtil->setMarkPeriod($markPeriodModuleCalculationRelation);
        $markPeriodModuleCalculationRelation->setModule($module);
        $markPeriodModuleCalculationRelation->setStudentCourseRegistration($studentCourseRegistration);
        $markPeriodModuleCalculationRelation->setClassProgram($classProgram);
        $markPeriodModuleCalculationRelation->setMarkCourseCalculated($markPeriodCourseCalculated);
        $markPeriodModuleCalculationRelation->setMarkModuleCalculated($markPeriodModuleCalculated);
        $markPeriodModuleCalculationRelation->setMarkCalculated($mark);
        $markPeriodModuleCalculationRelation->setMarkUsed($markUsed);
        $markPeriodModuleCalculationRelation->setAverageGPACalculated($averageGpaCalculated);
        $markPeriodModuleCalculationRelation->setGpaUsed($gpaUsed);
        $markPeriodModuleCalculationRelation->setCoeff($classProgram->getCoeff());
        $markPeriodModuleCalculationRelation->setGradeUsed($gradeUsed);
        $markPeriodModuleCalculationRelation->setValidationBase($validationBase);
        $markPeriodModuleCalculationRelation->setCoeff($coeff);
        $markPeriodModuleCalculationRelation->setIsCourseValidated($isCourseValidated);
    }
}