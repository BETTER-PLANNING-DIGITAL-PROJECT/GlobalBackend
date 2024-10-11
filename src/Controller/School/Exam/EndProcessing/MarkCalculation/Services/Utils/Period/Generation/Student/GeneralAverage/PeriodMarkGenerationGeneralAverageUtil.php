<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Generation\Student\GeneralAverage;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Generation\Student\PeriodMarkGenerationUtil;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\MarkGrade;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculated;
use App\Entity\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculated;
use App\Entity\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCourseCalculationRelation;
use App\Entity\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageSequenceCalculationRelation;
use App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculated;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\School\Study\Configuration\Module;
use App\Entity\School\Study\Program\ClassProgram;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculatedRepository;
use Doctrine\ORM\EntityManagerInterface;

// Classe contenant toutes les fonctions concernant la generation des notes de matiere pour une sequence
class PeriodMarkGenerationGeneralAverageUtil
{
    private string $generateCalculationRelationMethod;

    public function __construct(
        // Configurations
        string                                                $halfYearAverageFormula,
        // Attributs principaux
        private readonly EvaluationPeriod                     $evaluationPeriod,
        private readonly MarkPeriodCourseCalculatedRepository $markPeriodCourseCalculatedRepository,
        private readonly EntityManagerInterface               $entityManager,

        // Attributs principaux
        private PeriodMarkGenerationUtil                      $periodMarkGenerationUtil,
    )
    {
        $this->generateCalculationRelationMethod = 'generatePeriodGeneralAverageCalculationRelations'.$halfYearAverageFormula;
    }

    public function getPeriodMarkGenerationUtil(): PeriodMarkGenerationUtil
    {
        return $this->periodMarkGenerationUtil;
    }

    public function setPeriodMarkGenerationUtil(PeriodMarkGenerationUtil $periodMarkGenerationUtil): PeriodMarkGenerationGeneralAverageUtil
    {
        $this->periodMarkGenerationUtil = $periodMarkGenerationUtil;
        return $this;
    }

    // Generer la moyenne generale d'une periode
    function generatePeriodGeneralAverageCalculated(?float $average, ?float $averageGpa,int $numberOfAttendedCourses,int $numberOfComposedCourses,float $totalOfCreditsAttended,float $totalOfCreditsComposed,float $percentageSubjectNumber,
            float $percentageTotalCoefficient,bool $isClassed,array $markCalculateds): MarkPeriodGeneralAverageCalculated
    {

        $markPeriodGeneralAverageCalculated = new MarkPeriodGeneralAverageCalculated();
        $this->periodMarkGenerationUtil->setMarkPeriod($markPeriodGeneralAverageCalculated);
        $markPeriodGeneralAverageCalculated->setAverage($average);
        
        $markPeriodGeneralAverageCalculated->setAverageGpa($averageGpa);
        $markPeriodGeneralAverageCalculated->setNumberOfAttendedCourses($numberOfAttendedCourses);
        $markPeriodGeneralAverageCalculated->setNumberOfComposedCourses($numberOfComposedCourses);
        $markPeriodGeneralAverageCalculated->setTotalOfCreditsAttended($totalOfCreditsAttended);
        $markPeriodGeneralAverageCalculated->setTotalOfCreditsComposed($totalOfCreditsComposed);
        $markPeriodGeneralAverageCalculated->setPercentageSubjectNumber($percentageSubjectNumber);
        $markPeriodGeneralAverageCalculated->setPercentageTotalCoefficient($percentageTotalCoefficient);
        $markPeriodGeneralAverageCalculated->setIsClassed($isClassed);
//        $markPeriodGeneralAverageCalculated->setIsEliminated(!boolval($average));

        $this->periodMarkGenerationUtil->setMarkGrade($markPeriodGeneralAverageCalculated, $average);
        // ,ClassProgram $classProgram,NoteType $noteType,Mark $sequenceMark,MarkSequenceCourseCalculated $markPeriodGeneralAverageCalculated

        $this->{$this->generateCalculationRelationMethod}($markCalculateds,$average,$averageGpa,$markPeriodGeneralAverageCalculated);
        $this->entityManager->flush();
        return $markPeriodGeneralAverageCalculated;
    }

    // Generer la relation de calcul dans la table intermediaire
    // halfYearAverageFormula = 1
    function generatePeriodGeneralAverageCalculationRelation1(?Module $module, StudentCourseRegistration $studentCourseRegistration, ClassProgram $classProgram, MarkPeriodCourseCalculated $markCalculated, MarkPeriodGeneralAverageCalculated $markPeriodGeneralAverageCalculated, ?float $average, ?float $markUsed, ?float $averageGpaCalculated, ?float $gpaUsed, ?MarkGrade $gradeUsed, ?float $coeff, ?float $validationBase , string $isCourseValidated)
    {
        $markPeriodGeneralAverageCalculationRelation = new MarkPeriodGeneralAverageCourseCalculationRelation();
        $this->periodMarkGenerationUtil->setMarkPeriod($markPeriodGeneralAverageCalculationRelation);
        $markPeriodGeneralAverageCalculationRelation->setModule($module);
        $markPeriodGeneralAverageCalculationRelation->setStudentCourseRegistration($studentCourseRegistration);
        $markPeriodGeneralAverageCalculationRelation->setClassProgram($classProgram);
        $markPeriodGeneralAverageCalculationRelation->setMarkCourseCalculated($markCalculated);
        $markPeriodGeneralAverageCalculationRelation->setPeriodGeneralAverageCalculated($markPeriodGeneralAverageCalculated);
        $markPeriodGeneralAverageCalculationRelation->setAverageCalculated($average);
        $markPeriodGeneralAverageCalculationRelation->setMarkUsed($markUsed);
        $markPeriodGeneralAverageCalculationRelation->setAverageGPACalculated($averageGpaCalculated);
        $markPeriodGeneralAverageCalculationRelation->setGpaUsed($gpaUsed);
        $markPeriodGeneralAverageCalculationRelation->setGradeUsed($gradeUsed);
        $markPeriodGeneralAverageCalculationRelation->setValidationBase($validationBase);
        $markPeriodGeneralAverageCalculationRelation->setCoeff($coeff);
        $markPeriodGeneralAverageCalculationRelation->setIsCourseValidated($isCourseValidated);
    }

    function generatePeriodGeneralAverageCalculationRelations1(array $markCalculateds,?float $average,?float $averageGpa,MarkPeriodGeneralAverageCalculated $markPeriodGeneralAverageCalculated)
    {
        $totalCredit = $totalCreditValidated = $totalCreditConsidered = $totalMarks = 0;
        foreach ($markCalculateds as $markPeriodCourseCalculated) {
            $classProgram = $markPeriodCourseCalculated->getClassProgram();
            $credit = $classProgram->getCoeff();
            $isCourseValidated = $markPeriodCourseCalculated->getIsCourseValidated();
            $totalCourseMarksUsed = $markPeriodCourseCalculated->getTotal();

            if ($isCourseValidated !== 'nv'){
                if ($isCourseValidated === 'v') $totalCreditValidated += $credit;
                if (!$markPeriodCourseCalculated->getIsModuleEliminated()) $totalCreditConsidered += $credit;
            }

            $totalCredit += $credit;
            $totalMarks += $totalCourseMarksUsed;

            $studentCourseRegistration = $markPeriodCourseCalculated->getStudentCourseRegistration();
            $markUsed = $markPeriodCourseCalculated->getMark();
            $gradeUsed = $markPeriodCourseCalculated->getGrade();
            $gpaUsed = $gradeUsed?->getGpa();
            $coeff = $classProgram->getCoeff();
            $validationBase = $classProgram->getValidationBase();
            $module = $classProgram->getModule();

            $this->generatePeriodGeneralAverageCalculationRelation1($module,$studentCourseRegistration, $classProgram, $markPeriodCourseCalculated, $markPeriodGeneralAverageCalculated, $average,$markUsed,$averageGpa,$gpaUsed,$gradeUsed,$coeff,$validationBase,$isCourseValidated);
        }
        $markPeriodGeneralAverageCalculated->setTotalCredit($totalCredit);
        $markPeriodGeneralAverageCalculated->setTotal($totalMarks);
        $markPeriodGeneralAverageCalculated->setTotalCreditValidated($totalCreditValidated);
        $markPeriodGeneralAverageCalculated->setTotalCreditConsidered($totalCreditConsidered);
    }

    // Generer la relation de calcul dans la table intermediaire
    // halfYearAverageFormula = 2
    function generatePeriodGeneralAverageCalculationRelation2(Sequence $sequence, MarkSequenceGeneralAverageCalculated $markCalculated, MarkPeriodGeneralAverageCalculated $markPeriodGeneralAverageCalculated, ?float $average, ?float $averageUsed, ?float $averageGpaCalculated, ?float $gpaUsed, ?MarkGrade $gradeUsed,?float $generalEliminateAverage,bool $isEliminated,bool $isClassed)
    {
        $markPeriodGeneralAverageCalculationRelation = new MarkPeriodGeneralAverageSequenceCalculationRelation();
        $this->periodMarkGenerationUtil->setMarkPeriod($markPeriodGeneralAverageCalculationRelation);
        $markPeriodGeneralAverageCalculationRelation->setSequence($sequence);
        $markPeriodGeneralAverageCalculationRelation->setSequenceGeneralAverageCalculated($markCalculated);
        $markPeriodGeneralAverageCalculationRelation->setPeriodGeneralAverageCalculated($markPeriodGeneralAverageCalculated);
        $markPeriodGeneralAverageCalculationRelation->setAverageCalculated($average);
        $markPeriodGeneralAverageCalculationRelation->setAverageUsed($averageUsed);
        $markPeriodGeneralAverageCalculationRelation->setIsEliminated($markCalculated->getIsEliminated());
        $markPeriodGeneralAverageCalculationRelation->setGradeUsed($gradeUsed);
        $markPeriodGeneralAverageCalculationRelation->setAverageGpaCalculated($averageGpaCalculated);
        $markPeriodGeneralAverageCalculationRelation->setGpaUsed($gpaUsed);
        $markPeriodGeneralAverageCalculationRelation->setSequenceGeneralEliminateAverage($generalEliminateAverage);
        $markPeriodGeneralAverageCalculationRelation->setIsEliminated($isEliminated);
        $markPeriodGeneralAverageCalculationRelation->setIsClassed($isClassed);
    }

    function getTotalCredits(StudentRegistration $student): array
    {
        $markPeriodCourseCalculateds = $this->markPeriodCourseCalculatedRepository->findBy(['student' => $student,'evaluationPeriod' => $this->evaluationPeriod]);
        $totalCredits = $totalCreditsValidated = $totalCreditsConsidered = $totalMarks = 0;
        foreach ($markPeriodCourseCalculateds as $markPeriodCourseCalculated) {
            $credit = $markPeriodCourseCalculated->getCoeff();
            $isCourseValidated = $markPeriodCourseCalculated->getIsCourseValidated();
            $totalCourseMarksUsed = $markPeriodCourseCalculated->getTotal();
            if ($isCourseValidated !== 'nv'){
                if ($isCourseValidated === 'v') $totalCreditsValidated += $credit;
                if (!$markPeriodCourseCalculated->getIsModuleEliminated()) $totalCreditsConsidered += $credit;
            }

            $totalCredits += $credit;
            $totalMarks += $totalCourseMarksUsed;
        }
        return ['totalCredits'=>$totalCredits,'totalCreditsValidated'=>$totalCreditsValidated,'totalCreditsConsidered'=>$totalCreditsConsidered,'totalMarks'=>$totalMarks];
    }

    function generatePeriodGeneralAverageCalculationRelations2(array $markCalculateds,?float $average,?float $averageGpa,MarkPeriodGeneralAverageCalculated $markPeriodGeneralAverageCalculated)
    {
        foreach ($markCalculateds as $markSequenceGeneralAverageCalculated) {

            if ($markSequenceGeneralAverageCalculated) {
                $sequence = $markSequenceGeneralAverageCalculated->getSequence();
                $averageUsed = $markSequenceGeneralAverageCalculated->getAverage();
                $gradeUsed = $markSequenceGeneralAverageCalculated->getGrade();
                $gpaUsed = $gradeUsed?->getGpa();
                $generalEliminateAverage = $sequence->getEliminateAverage();
                $isClassed = $markSequenceGeneralAverageCalculated->getIsClassed();
                $isEliminated = $markSequenceGeneralAverageCalculated->getIsEliminated();
                $this->generatePeriodGeneralAverageCalculationRelation2($sequence, $markSequenceGeneralAverageCalculated, $markPeriodGeneralAverageCalculated, $average, $averageUsed, $averageGpa, $gpaUsed, $gradeUsed, $generalEliminateAverage, $isClassed, $isEliminated);
            }
        }
        $totalCreditResult = $this->getTotalCredits($this->periodMarkGenerationUtil->getStudent());
        extract($totalCreditResult);
        $markPeriodGeneralAverageCalculated->setTotalCredit($totalCredits);
        $markPeriodGeneralAverageCalculated->setTotal($totalMarks);
        $markPeriodGeneralAverageCalculated->setTotalCreditValidated($totalCreditsValidated);
        $markPeriodGeneralAverageCalculated->setTotalCreditConsidered($totalCreditsConsidered);
    }
}