<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Generation\Student\Module;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Generation\Student\AnnualMarkGenerationUtil;
use App\Entity\School\Exam\Configuration\MarkGrade;
use App\Entity\School\Exam\Operation\Annual\Course\MarkAnnualCourseCalculated;
use App\Entity\School\Exam\Operation\Annual\Module\MarkAnnualModuleCalculated;
use App\Entity\School\Exam\Operation\Annual\Module\MarkAnnualModuleCalculationRelation;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Study\Configuration\Module;
use App\Entity\School\Study\Program\ClassProgram;
use Doctrine\ORM\EntityManagerInterface;

// Classe contenant toutes les fonctions concernant la generation des notes de matiere pour une sequence
class AnnualMarkGenerationModuleUtil
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,

        // Attributs principaux
        private AnnualMarkGenerationUtil        $annualMarkGenerationUtil,
    )
    {

    }

    public function getAnnualMarkGenerationUtil(): AnnualMarkGenerationUtil
    {
        return $this->annualMarkGenerationUtil;
    }

    public function setAnnualMarkGenerationUtil(AnnualMarkGenerationUtil $annualMarkGenerationUtil): AnnualMarkGenerationModuleUtil
    {
        $this->annualMarkGenerationUtil = $annualMarkGenerationUtil;
        return $this;
    }


    // Generer la note d'un module d'une sequence
    function generateAnnualModuleCalculated(Module $module, ?float $mark, ?float $averageGpa, ?float $validationBase, bool $isModulated, bool $isEliminated, array $markAnnualCoursesCalculated): array
    {

        $markAnnualModuleCalculated = new MarkAnnualModuleCalculated();
        $this->annualMarkGenerationUtil->setMarkAnnual($markAnnualModuleCalculated);
        $markAnnualModuleCalculated->setModule($module);
        $markAnnualModuleCalculated->setMark($mark);

        $markAnnualModuleCalculated->setAverageGpa($averageGpa);
        $markAnnualModuleCalculated->setIsEliminated($isEliminated);
        $this->annualMarkGenerationUtil->setMarkGrade($markAnnualModuleCalculated, $mark);
        // ,ClassProgram $classProgram,NoteType $noteType,Mark $sequenceMark,MarkSequenceCourseCalculated $markAnnualCourseCalculated

        $markAnnualModulesCalculatedArray = [];
        $totalCreditValidated = $totalCredit = $totalCreditConsidered = $totalMarks = 0;
        if (!$isModulated) {
            if ($isEliminated) {
                foreach ($markAnnualCoursesCalculated as $markAnnualCourseCalculated) {
                    $classProgram = $markAnnualCourseCalculated->getClassProgram();
                    $markUsed = $markAnnualCourseCalculated->getMark();
                    $gradeUsed = $markAnnualCourseCalculated->getGrade();
                    $totalCourseMarksUsed = $markAnnualCourseCalculated->getTotal();
                    $gpaUsed = $gradeUsed?->getGpa();
                    $coeff = $classProgram->getCoeff();
                    $isCourseValidated = $markAnnualCourseCalculated->getIsCourseValidated();

                    if ($isCourseValidated === 'v') {
                        $totalCreditValidated += $coeff;
                    }
                    $totalCredit += $coeff;
                    $totalMarks += $totalCourseMarksUsed;
                    $markAnnualCourseCalculated->setIsModuleEliminated(true);
                    $markAnnualModulesCalculatedArray[] = $markAnnualCourseCalculated;
                    $this->generateAnnualModuleCalculationRelation($module, $classProgram, $markAnnualCourseCalculated, $markAnnualModuleCalculated, $mark, $markUsed, $averageGpa, $gpaUsed, $gradeUsed, $coeff, $validationBase, $isCourseValidated);
                }
            } else {
                foreach ($markAnnualCoursesCalculated as $markAnnualCourseCalculated) {
                    $classProgram = $markAnnualCourseCalculated->getClassProgram();
                    $markUsed = $markAnnualCourseCalculated->getMark();
                    $gradeUsed = $markAnnualCourseCalculated->getGrade();
                    $totalCourseMarksUsed = $markAnnualCourseCalculated->getTotal();
                    $gpaUsed = $gradeUsed?->getGpa();
                    $coeff = $classProgram->getCoeff();
                    $isCourseValidated = $markAnnualCourseCalculated->getIsCourseValidated();

                    if ($isCourseValidated === 'v') {
                        $totalCreditValidated += $coeff;
                    }

                    $totalCredit += $coeff;
                    $totalMarks += $totalCourseMarksUsed;
                    $markAnnualCourseCalculated->setIsModuleEliminated(false);
                    $markAnnualModulesCalculatedArray[] = $markAnnualCourseCalculated;
                    $this->generateAnnualModuleCalculationRelation($module, $classProgram, $markAnnualCourseCalculated, $markAnnualModuleCalculated, $mark, $markUsed, $averageGpa, $gpaUsed, $gradeUsed, $coeff, $validationBase, $isCourseValidated);
                }
                $totalCreditConsidered = $totalCreditValidated;
            }
        } else {
            foreach ($markAnnualCoursesCalculated as $markAnnualCourseCalculated) {
                $classProgram = $markAnnualCourseCalculated->getClassProgram();
                $markUsed = $markAnnualCourseCalculated->getMark();
                $gradeUsed = $markAnnualCourseCalculated->getGrade();
                $totalCourseMarksUsed = $markAnnualCourseCalculated->getTotal();
                $gpaUsed = $gradeUsed?->getGpa();
                $coeff = $classProgram->getCoeff();
                $isCourseValidated = $markAnnualCourseCalculated->getIsCourseValidated();

                if ($isCourseValidated === 'nv') {
                    $isCourseValidated = 'm';
                    $markAnnualCourseCalculated->setIsCourseValidated($isCourseValidated);
                } else {
                    $totalCreditValidated += $coeff;
                }
                $totalCredit += $coeff;
                $totalMarks += $totalCourseMarksUsed;

                $markAnnualModulesCalculatedArray[] = $markAnnualCourseCalculated;
                $this->generateAnnualModuleCalculationRelation($module, $classProgram, $markAnnualCourseCalculated, $markAnnualModuleCalculated, $mark, $markUsed, $averageGpa, $gpaUsed, $gradeUsed, $coeff, $validationBase, $isCourseValidated);
            }
            $totalCreditConsidered = $totalCredit;
        }
        $markAnnualModuleCalculated->setTotalCredit($totalCredit);
        $markAnnualModuleCalculated->setTotal($totalMarks);
        $markAnnualModuleCalculated->setTotalCreditValidated($totalCreditValidated);
        $markAnnualModuleCalculated->setTotalCreditConsidered($totalCreditConsidered);

        $markAnnualModulesCalculatedArray[] = $markAnnualModuleCalculated;
        $this->entityManager->flush();
        return $markAnnualModulesCalculatedArray;
    }

    // Generer la relation de calcul dans la table intermediaire
    function generateAnnualModuleCalculationRelation(Module $module, ClassProgram $classProgram, MarkAnnualCourseCalculated $markAnnualCourseCalculated, MarkAnnualModuleCalculated $markAnnualModuleCalculated, ?float $mark, ?float $markUsed, ?float $averageGpaCalculated, ?float $gpaUsed, ?MarkGrade $gradeUsed, ?float $coeff, ?float $validationBase, string $isCourseValidated)
    {
        $markAnnualModuleCalculationRelation = new MarkAnnualModuleCalculationRelation();
        $this->annualMarkGenerationUtil->setMarkAnnual($markAnnualModuleCalculationRelation);
        $markAnnualModuleCalculationRelation->setModule($module);
        $markAnnualModuleCalculationRelation->setClassProgram($classProgram);
        $markAnnualModuleCalculationRelation->setMarkCourseCalculated($markAnnualCourseCalculated);
        $markAnnualModuleCalculationRelation->setMarkModuleCalculated($markAnnualModuleCalculated);
        $markAnnualModuleCalculationRelation->setMarkCalculated($mark);
        $markAnnualModuleCalculationRelation->setMarkUsed($markUsed);
        $markAnnualModuleCalculationRelation->setAverageGPACalculated($averageGpaCalculated);
        $markAnnualModuleCalculationRelation->setGpaUsed($gpaUsed);
        $markAnnualModuleCalculationRelation->setCoeff($classProgram->getCoeff());
        $markAnnualModuleCalculationRelation->setGradeUsed($gradeUsed);
        $markAnnualModuleCalculationRelation->setValidationBase($validationBase);
        $markAnnualModuleCalculationRelation->setCoeff($coeff);
        $markAnnualModuleCalculationRelation->setIsCourseValidated($isCourseValidated);
    }
}