<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Generation\Student\GeneralAverage;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Generation\Student\AnnualMarkGenerationUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Generation\Student\PeriodMarkGenerationUtil;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\MarkGrade;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCalculated;
use App\Entity\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation;
use App\Entity\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCourseTernaryCalculationRelation;
use App\Entity\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAveragePeriodCalculationRelation;
use App\Entity\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageSequenceCalculationRelation;
use App\Entity\School\Exam\Operation\Annual\Course\MarkAnnualCourseCalculated;
use App\Entity\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculated;
use App\Entity\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculated;
use App\Entity\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCourseCalculationRelation;
use App\Entity\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageSequenceCalculationRelation;
use App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculated;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\School\Study\Configuration\Module;
use App\Entity\School\Study\Program\ClassProgram;
use App\Repository\School\Exam\Operation\Annual\Course\MarkAnnualCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculatedRepository;
use Doctrine\ORM\EntityManagerInterface;

// Classe contenant toutes les fonctions concernant la generation des notes de matiere pour une sequence
class AnnualMarkGenerationGeneralAverageUtil
{
    private string $generateCalculationRelationMethod;
    private string $generateCourseCalculationRelationMethod;
    private string $getTotalCreditsMethod;

    public function __construct( 
        // Configurations
        string                                                $finalAverageFormula,
        string                                                $schoolSystem,
        // Attributs principaux
        private readonly array                                $evaluationPeriods,
        private readonly MarkPeriodCourseCalculatedRepository $markPeriodCourseCalculatedRepository,
        private readonly MarkAnnualCourseCalculatedRepository $markAnnualCourseCalculatedRepository,
        private readonly EntityManagerInterface               $entityManager,

        // Attributs principaux
        private AnnualMarkGenerationUtil                      $annualMarkGenerationUtil,
    )
    {
        $this->generateCalculationRelationMethod = 'generateAnnualGeneralAverageCalculationRelations'.$finalAverageFormula;
        $this->getTotalCreditsMethod = 'getTotalCredits'.($schoolSystem === 'ternary' ? 1 : 2);
        $this->generateCourseCalculationRelationMethod = 'generateAnnualGeneralAverageCalculationRelation1'.($schoolSystem === 'ternary' ? 1 : 2);
    }

    public function getAnnualMarkGenerationUtil(): AnnualMarkGenerationUtil
    {
        return $this->annualMarkGenerationUtil;
    }

    public function setAnnualMarkGenerationUtil(AnnualMarkGenerationUtil $annualMarkGenerationUtil): AnnualMarkGenerationGeneralAverageUtil
    {
        $this->annualMarkGenerationUtil = $annualMarkGenerationUtil;
        return $this;
    }

    // Generer la moyenne generale d'une sequence
    function generateAnnualGeneralAverageCalculated(?float $average, ?float $averageGpa,int $numberOfAttendedCourses,int $numberOfComposedCourses,float $totalOfCreditsAttended,float $totalOfCreditsComposed,float $percentageSubjectNumber,
            float $percentageTotalCoefficient,bool $isClassed,array $markCalculateds): MarkAnnualGeneralAverageCalculated
    {

        $markAnnualGeneralAverageCalculated = new MarkAnnualGeneralAverageCalculated();
        $this->annualMarkGenerationUtil->setMarkAnnual($markAnnualGeneralAverageCalculated);
        $markAnnualGeneralAverageCalculated->setAverage($average);
        
        $markAnnualGeneralAverageCalculated->setAverageGpa($averageGpa);
        $markAnnualGeneralAverageCalculated->setNumberOfAttendedCourses($numberOfAttendedCourses);
        $markAnnualGeneralAverageCalculated->setNumberOfComposedCourses($numberOfComposedCourses);
        $markAnnualGeneralAverageCalculated->setTotalOfCreditsAttended($totalOfCreditsAttended);
        $markAnnualGeneralAverageCalculated->setTotalOfCreditsComposed($totalOfCreditsComposed);
        $markAnnualGeneralAverageCalculated->setPercentageSubjectNumber($percentageSubjectNumber);
        $markAnnualGeneralAverageCalculated->setPercentageTotalCoefficient($percentageTotalCoefficient);
        $markAnnualGeneralAverageCalculated->setIsClassed($isClassed);
//        $markAnnualGeneralAverageCalculated->setIsEliminated(!boolval($average));

        $this->annualMarkGenerationUtil->setMarkGrade($markAnnualGeneralAverageCalculated, $average);
        // ,ClassProgram $classProgram,NoteType $noteType,Mark $sequenceMark,MarkSequenceCourseCalculated $markAnnualGeneralAverageCalculated

        $this->{$this->generateCalculationRelationMethod}($markCalculateds,$average,$averageGpa,$markAnnualGeneralAverageCalculated);
        $this->entityManager->flush();
        return $markAnnualGeneralAverageCalculated;
    }

    // Generer la relation de calcul dans la table intermediaire
    // halfYearAverageFormula = 1
    // Pour le superieur c'est pour ca qu'il y a 1 apres 1
    function generateAnnualGeneralAverageCalculationRelation11(?Module $module, ClassProgram $classProgram, MarkPeriodCourseCalculated $markCalculated, MarkAnnualGeneralAverageCalculated $markAnnualGeneralAverageCalculated, ?float $average, ?float $markUsed, ?float $averageGpaCalculated, ?float $gpaUsed, ?MarkGrade $gradeUsed, ?float $coeff, ?float $validationBase , string $isCourseValidated)
    {
        $markAnnualGeneralAverageCalculationRelation = new MarkAnnualGeneralAverageCourseTernaryCalculationRelation();
        $this->annualMarkGenerationUtil->setMarkAnnual($markAnnualGeneralAverageCalculationRelation);
        $markAnnualGeneralAverageCalculationRelation->setModule($module);
        $markAnnualGeneralAverageCalculationRelation->setClassProgram($classProgram);
        $markAnnualGeneralAverageCalculationRelation->setEvaluationPeriod($classProgram->getEvaluationPeriod());
        $markAnnualGeneralAverageCalculationRelation->setMarkCourseCalculated($markCalculated);
        $markAnnualGeneralAverageCalculationRelation->setAnnualGeneralAverageCalculated($markAnnualGeneralAverageCalculated);
        $markAnnualGeneralAverageCalculationRelation->setAverageCalculated($average);
        $markAnnualGeneralAverageCalculationRelation->setMarkUsed($markUsed);
        $markAnnualGeneralAverageCalculationRelation->setAverageGPACalculated($averageGpaCalculated);
        $markAnnualGeneralAverageCalculationRelation->setGpaUsed($gpaUsed);
        $markAnnualGeneralAverageCalculationRelation->setGradeUsed($gradeUsed);
        $markAnnualGeneralAverageCalculationRelation->setValidationBase($validationBase);
        $markAnnualGeneralAverageCalculationRelation->setCoeff($coeff);
        $markAnnualGeneralAverageCalculationRelation->setIsCourseValidated($isCourseValidated);
    }

    // Pour le primaire et secondaire c'est pour ca qu'il y a 2 apres 1
    function generateAnnualGeneralAverageCalculationRelation12(?Module $module, ClassProgram $classProgram, MarkAnnualCourseCalculated $markCalculated, MarkAnnualGeneralAverageCalculated $markAnnualGeneralAverageCalculated, ?float $average, ?float $markUsed, ?float $averageGpaCalculated, ?float $gpaUsed, ?MarkGrade $gradeUsed, ?float $coeff, ?float $validationBase , string $isCourseValidated)
    {
        $markAnnualGeneralAverageCalculationRelation = new MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation();
        $this->annualMarkGenerationUtil->setMarkAnnual($markAnnualGeneralAverageCalculationRelation);
        $markAnnualGeneralAverageCalculationRelation->setModule($module);
        $markAnnualGeneralAverageCalculationRelation->setClassProgram($classProgram);
        $markAnnualGeneralAverageCalculationRelation->setEvaluationPeriod($classProgram->getEvaluationPeriod());
        $markAnnualGeneralAverageCalculationRelation->setMarkCourseCalculated($markCalculated);
        $markAnnualGeneralAverageCalculationRelation->setAnnualGeneralAverageCalculated($markAnnualGeneralAverageCalculated);
        $markAnnualGeneralAverageCalculationRelation->setAverageCalculated($average);
        $markAnnualGeneralAverageCalculationRelation->setMarkUsed($markUsed);
        $markAnnualGeneralAverageCalculationRelation->setAverageGPACalculated($averageGpaCalculated);
        $markAnnualGeneralAverageCalculationRelation->setGpaUsed($gpaUsed);
        $markAnnualGeneralAverageCalculationRelation->setGradeUsed($gradeUsed);
        $markAnnualGeneralAverageCalculationRelation->setValidationBase($validationBase);
        $markAnnualGeneralAverageCalculationRelation->setCoeff($coeff);
        $markAnnualGeneralAverageCalculationRelation->setIsCourseValidated($isCourseValidated);
    }

    function generateAnnualGeneralAverageCalculationRelations1(array $markCalculateds,?float $average,?float $averageGpa,MarkAnnualGeneralAverageCalculated $markAnnualGeneralAverageCalculated)
    {
        $totalCredit = $totalCreditValidated = $totalCreditConsidered = $totalMarks = 0;
        foreach ($markCalculateds as $markAnnualCourseCalculated) {
            $classProgram = $markAnnualCourseCalculated->getClassProgram();
            $credit = $classProgram->getCoeff();
            $isCourseValidated = $markAnnualCourseCalculated->getIsCourseValidated();
            $totalCourseMarks = $markAnnualCourseCalculated->getTotal();

            if ($isCourseValidated !== 'nv'){
                if ($isCourseValidated === 'v') $totalCreditValidated += $credit;
                if (!$markAnnualCourseCalculated->getIsModuleEliminated()) $totalCreditConsidered += $credit;
            }

            $totalCredit += $credit;
            $totalMarks += $totalCourseMarks;

            $markUsed = $markAnnualCourseCalculated->getMark();
            $gradeUsed = $markAnnualCourseCalculated->getGrade();
            $gpaUsed = $gradeUsed?->getGpa();
            $coeff = $classProgram->getCoeff();
            $validationBase = $classProgram->getValidationBase();
            $module = $classProgram->getModule();

            $this->{$this->generateCourseCalculationRelationMethod}($module, $classProgram, $markAnnualCourseCalculated, $markAnnualGeneralAverageCalculated, $average,$markUsed,$averageGpa,$gpaUsed,$gradeUsed,$coeff,$validationBase,$isCourseValidated);
        }
        $markAnnualGeneralAverageCalculated->setTotal($totalMarks);
        $markAnnualGeneralAverageCalculated->setTotalCredit($totalCredit);
        $markAnnualGeneralAverageCalculated->setTotalCreditValidated($totalCreditValidated);
        $markAnnualGeneralAverageCalculated->setTotalCreditConsidered($totalCreditConsidered);
    }

    // Generer la relation de calcul dans la table intermediaire
    // halfYearAverageFormula = 2 
    function generateAnnualGeneralAverageCalculationRelation2(Sequence $sequence,EvaluationPeriod $evaluationPeriod, MarkSequenceGeneralAverageCalculated $markCalculated, MarkAnnualGeneralAverageCalculated $markAnnualGeneralAverageCalculated, ?float $average, ?float $averageUsed, ?float $averageGpaCalculated, ?float $gpaUsed, ?MarkGrade $gradeUsed,?float $generalEliminateAverage,bool $isEliminated,bool $isClassed)
    {
        $markAnnualGeneralAverageCalculationRelation = new MarkAnnualGeneralAverageSequenceCalculationRelation();
        $this->annualMarkGenerationUtil->setMarkAnnual($markAnnualGeneralAverageCalculationRelation);
        $markAnnualGeneralAverageCalculationRelation->setSequence($sequence);
        $markAnnualGeneralAverageCalculationRelation->setEvaluationPeriod($evaluationPeriod);
        $markAnnualGeneralAverageCalculationRelation->setSequenceGeneralAverageCalculated($markCalculated);
        $markAnnualGeneralAverageCalculationRelation->setAnnualGeneralAverageCalculated($markAnnualGeneralAverageCalculated);
        $markAnnualGeneralAverageCalculationRelation->setAverageCalculated($average);
        $markAnnualGeneralAverageCalculationRelation->setAverageUsed($averageUsed);
        $markAnnualGeneralAverageCalculationRelation->setIsEliminated($markCalculated->getIsEliminated());
        $markAnnualGeneralAverageCalculationRelation->setGradeUsed($gradeUsed);
        $markAnnualGeneralAverageCalculationRelation->setAverageGpaCalculated($averageGpaCalculated);
        $markAnnualGeneralAverageCalculationRelation->setGpaUsed($gpaUsed);
        $markAnnualGeneralAverageCalculationRelation->setSequenceGeneralEliminateAverage($generalEliminateAverage);
        $markAnnualGeneralAverageCalculationRelation->setIsEliminated($isEliminated);
        $markAnnualGeneralAverageCalculationRelation->setIsClassed($isClassed);
    }

    // Decompte du total des credits dans le cas du superieur
    function getTotalCredits1(StudentRegistration $student): array
    {
        $totalCredits = $totalCreditsValidated = $totalCreditsConsidered = $totalMarks = 0;
        foreach ($this->evaluationPeriods as $evaluationPeriod) {
            $markPeriodCourseCalculateds = $this->markPeriodCourseCalculatedRepository->findBy(['student' => $student, 'evaluationPeriod' => $evaluationPeriod]);
            foreach ($markPeriodCourseCalculateds as $markPeriodCourseCalculated) {
                $credit = $markPeriodCourseCalculated->getCoeff();
                $isCourseValidated = $markPeriodCourseCalculated->getIsCourseValidated();
                $totalCourseMarks = $markPeriodCourseCalculated->getTotal();
                if ($isCourseValidated !== 'nv') {
                    if ($isCourseValidated === 'v') $totalCreditsValidated += $credit;
                    if (!$markPeriodCourseCalculated->getIsModuleEliminated()) $totalCreditsConsidered += $credit;
                }
                $totalCredits += $credit;
                $totalMarks += $totalCourseMarks;
            }
        }
        return ['totalCredits'=>$totalCredits,'totalCreditsValidated'=>$totalCreditsValidated,'totalCreditsConsidered'=>$totalCreditsConsidered,'total'=>$totalMarks];
    }

    function getTotalCredits(StudentRegistration $student): array{
        return $this->{$this->getTotalCreditsMethod}($student);
    }

    // Decompte du total des credits dans le cas du secondaire / primaire
    function getTotalCredits2(StudentRegistration $student): array
    {
        $totalCredits = $totalCreditsValidated = $totalCreditsConsidered = $totalMarks = 0;
        $markAnnualCourseCalculateds = $this->markAnnualCourseCalculatedRepository->findBy(['student' => $student]);
        foreach ($markAnnualCourseCalculateds as $markAnnualCourseCalculated) {
            $credit = $markAnnualCourseCalculated->getCoeff();
            $isCourseValidated = $markAnnualCourseCalculated->getIsCourseValidated();
            $totalCourseMarks = $markAnnualCourseCalculated->getTotal();
            if ($isCourseValidated !== 'nv') {
                if ($isCourseValidated === 'v') $totalCreditsValidated += $credit;
                if (!$markAnnualCourseCalculated->getIsModuleEliminated()) $totalCreditsConsidered += $credit;
            }
            $totalCredits += $credit;
            $totalMarks += $totalCourseMarks;
        }
        return ['totalCredits'=>$totalCredits,'totalCreditsValidated'=>$totalCreditsValidated,'totalCreditsConsidered'=>$totalCreditsConsidered,'total'=>$totalMarks];
    }

    function generateAnnualGeneralAverageCalculationRelations2(array $markCalculateds,?float $average,?float $averageGpa,MarkAnnualGeneralAverageCalculated $markAnnualGeneralAverageCalculated)
    {
        foreach ($markCalculateds as $markSequenceGeneralAverageCalculated) {
            if ($markSequenceGeneralAverageCalculated) {
                $sequence = $markSequenceGeneralAverageCalculated->getSequence();
                $evaluationPeriod = $markSequenceGeneralAverageCalculated->getEvaluationPeriod();
                $averageUsed = $markSequenceGeneralAverageCalculated->getAverage();
                $gradeUsed = $markSequenceGeneralAverageCalculated->getGrade();
                $gpaUsed = $gradeUsed?->getGpa();
                $generalEliminateAverage = $sequence->getEliminateAverage();
                $isClassed = $markSequenceGeneralAverageCalculated->getIsClassed();
                $isEliminated = $markSequenceGeneralAverageCalculated->getIsEliminated();
                $this->generateAnnualGeneralAverageCalculationRelation2($sequence,$evaluationPeriod, $markSequenceGeneralAverageCalculated, $markAnnualGeneralAverageCalculated, $average, $averageUsed, $averageGpa, $gpaUsed, $gradeUsed, $generalEliminateAverage, $isClassed, $isEliminated);
            }
        }
        $totalCreditResult = $this->getTotalCredits($this->annualMarkGenerationUtil->getStudent());
        extract($totalCreditResult);
        $markAnnualGeneralAverageCalculated->setTotal($total);
        $markAnnualGeneralAverageCalculated->setTotalCredit($totalCredits);
        $markAnnualGeneralAverageCalculated->setTotalCreditValidated($totalCreditsValidated);
        $markAnnualGeneralAverageCalculated->setTotalCreditConsidered($totalCreditsConsidered);
    }

    // Generer la relation de calcul dans la table intermediaire
    // halfYearAverageFormula = 3 La moyenne des moyennes des periodes
    function generateAnnualGeneralAverageCalculationRelation3(EvaluationPeriod $evaluationPeriod, MarkPeriodGeneralAverageCalculated $markCalculated, MarkAnnualGeneralAverageCalculated $markAnnualGeneralAverageCalculated, ?float $average, ?float $averageUsed, ?float $averageGpaCalculated, ?float $gpaUsed, ?MarkGrade $gradeUsed,bool $isEliminated,bool $isClassed)
    {
        $markAnnualGeneralAverageCalculationRelation = new MarkAnnualGeneralAveragePeriodCalculationRelation();
        $this->annualMarkGenerationUtil->setMarkAnnual($markAnnualGeneralAverageCalculationRelation);
        $markAnnualGeneralAverageCalculationRelation->setEvaluationPeriod($evaluationPeriod);
        $markAnnualGeneralAverageCalculationRelation->setPeriodGeneralAverageCalculated($markCalculated);
        $markAnnualGeneralAverageCalculationRelation->setAnnualGeneralAverageCalculated($markAnnualGeneralAverageCalculated);
        $markAnnualGeneralAverageCalculationRelation->setAverageCalculated($average);
        $markAnnualGeneralAverageCalculationRelation->setAverageUsed($averageUsed);
        $markAnnualGeneralAverageCalculationRelation->setIsEliminated($markCalculated->getIsEliminated());
        $markAnnualGeneralAverageCalculationRelation->setGradeUsed($gradeUsed);
        $markAnnualGeneralAverageCalculationRelation->setAverageGpaCalculated($averageGpaCalculated);
        $markAnnualGeneralAverageCalculationRelation->setGpaUsed($gpaUsed);
        $markAnnualGeneralAverageCalculationRelation->setIsEliminated($isEliminated);
        $markAnnualGeneralAverageCalculationRelation->setIsClassed($isClassed);
    }

    function generateAnnualGeneralAverageCalculationRelations3(array $markCalculateds,?float $average,?float $averageGpa,MarkAnnualGeneralAverageCalculated $markAnnualGeneralAverageCalculated)
    {
        foreach ($markCalculateds as $markPeriodGeneralAverageCalculated) {
            if ($markPeriodGeneralAverageCalculated) {
                $evaluationPeriod = $markPeriodGeneralAverageCalculated->getEvaluationPeriod();
                $averageUsed = $markPeriodGeneralAverageCalculated->getAverage();
                $gradeUsed = $markPeriodGeneralAverageCalculated->getGrade();
                $gpaUsed = $gradeUsed?->getGpa();
                $isClassed = $markPeriodGeneralAverageCalculated->getIsClassed();
                $isEliminated = $markPeriodGeneralAverageCalculated->getIsEliminated();
                $this->generateAnnualGeneralAverageCalculationRelation3($evaluationPeriod, $markPeriodGeneralAverageCalculated, $markAnnualGeneralAverageCalculated, $average, $averageUsed, $averageGpa, $gpaUsed, $gradeUsed, $isClassed, $isEliminated);
            }
        }
        $totalCreditResult = $this->getTotalCredits($this->annualMarkGenerationUtil->getStudent());
        extract($totalCreditResult);
        $markAnnualGeneralAverageCalculated->setTotal($total);
        $markAnnualGeneralAverageCalculated->setTotalCredit($totalCredits);
        $markAnnualGeneralAverageCalculated->setTotalCreditValidated($totalCreditsValidated);
        $markAnnualGeneralAverageCalculated->setTotalCreditConsidered($totalCreditsConsidered);
    }
}