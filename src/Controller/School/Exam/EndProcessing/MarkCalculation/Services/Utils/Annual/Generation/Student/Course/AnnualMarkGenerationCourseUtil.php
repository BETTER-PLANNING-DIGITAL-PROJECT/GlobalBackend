<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Generation\Student\Course;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Generation\Student\AnnualMarkGenerationUtil;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\NoteType;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Annual\Course\MarkAnnualCourseCalculated;
use App\Entity\School\Exam\Operation\Annual\Course\MarkAnnualCoursePeriodCalculationRelation;
use App\Entity\School\Exam\Operation\Mark;
use App\Entity\School\Exam\Operation\Annual\Course\MarkAnnualCourseSequenceCalculationRelation;
use App\Entity\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculated;
use App\Entity\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculated;
use App\Entity\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculationRelation;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Study\Configuration\Module;
use App\Entity\School\Study\Program\ClassProgram;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;

// Classe contenant toutes les fonctions concernant la generation des notes de matiere pour une sequence
class AnnualMarkGenerationCourseUtil
{
    private string $generateCalculationRelationMethod;

    public function __construct(
        // Configurations
        string                                                $finalMarkFormula,
        // Repository
        private EntityManagerInterface   $entityManager,

        // Attributs principaux
        private AnnualMarkGenerationUtil $annualMarkGenerationUtil,
    )
    {
        $this->generateCalculationRelationMethod = 'generateAnnualCourseCalculationRelations'.$finalMarkFormula;
    }

    public function getAnnualMarkGenerationUtil(): AnnualMarkGenerationUtil
    {
        return $this->annualMarkGenerationUtil;
    }

    public function setAnnualMarkGenerationUtil(AnnualMarkGenerationUtil $annualMarkGenerationUtil): AnnualMarkGenerationCourseUtil
    {
        $this->annualMarkGenerationUtil = $annualMarkGenerationUtil;
        return $this;
    }

    // Generer la note d'une matieres d'une sequence
    // On retourne l'elmt ajoute pour ajouter l'attribut isEliminated s'il y a les eliminations
    function generateAnnualCourseCalculated( ?float $mark, string $isCourseValidated, array $courseMarks, ClassProgram $classProgram, ?float $validationBase, ?float $coeff): MarkAnnualCourseCalculated
    {

        $markAnnualCourseCalculated = new MarkAnnualCourseCalculated();
        $this->annualMarkGenerationUtil->setMarkAnnual($markAnnualCourseCalculated);
        $markAnnualCourseCalculated->setMark($mark);

        $markAnnualCourseCalculated->setClassProgram($classProgram);
        $module = $classProgram->getModule();
        $markAnnualCourseCalculated->setModule($module);
        $markAnnualCourseCalculated->setIsCourseValidated($isCourseValidated);
        $markAnnualCourseCalculated->setValidationBase($validationBase);
        $markAnnualCourseCalculated->setCoeff($coeff);

        $markAnnualCourseCalculated->setTotal(isset($mark) ? $mark * $coeff : null);
        $this->annualMarkGenerationUtil->setMarkGrade($markAnnualCourseCalculated, $mark);
        $this->generateAnnualCourseCalculationRelations($mark,$courseMarks,$classProgram,$markAnnualCourseCalculated,$module);
        $this->entityManager->flush();
        return $markAnnualCourseCalculated;
    }

    // Generer les relations de calcul dans la table intermediaire (Methode generale)
    function generateAnnualCourseCalculationRelations( ?float $mark, array $courseMarks, ClassProgram $classProgram,MarkAnnualCourseCalculated $markAnnualCourseCalculated,?Module $module){
        $this->{$this->generateCalculationRelationMethod}($mark,$courseMarks,$classProgram,$markAnnualCourseCalculated,$module);
    }

    // Generer la relation de calcul dans la table intermediaire
    function generateAnnualCourseCalculationRelation1(Sequence $sequence, StudentCourseRegistration $studentCourseRegistration, ClassProgram $classProgram, ?Module $module, MarkSequenceCourseCalculated $markSequenceCourseCalculated, MarkAnnualCourseCalculated $markAnnualCourseCalculated, ?float $markUsed, ?float $markCalculated)
    {
        $markAnnualCourseSequenceCalculationRelation = new MarkAnnualCourseSequenceCalculationRelation();
        $this->annualMarkGenerationUtil->setMarkAnnual($markAnnualCourseSequenceCalculationRelation);
        $markAnnualCourseSequenceCalculationRelation->setStudentCourseRegistration($studentCourseRegistration);
        $markAnnualCourseSequenceCalculationRelation->setClassProgram($classProgram);
        $markAnnualCourseSequenceCalculationRelation->setModule($module);
        $markAnnualCourseSequenceCalculationRelation->setMarkSequenceCourseCalculated($markSequenceCourseCalculated);
        $markAnnualCourseSequenceCalculationRelation->setMarkAnnualCourseCalculated($markAnnualCourseCalculated);
        $markAnnualCourseSequenceCalculationRelation->setMarkCalculated($markCalculated);
        $markAnnualCourseSequenceCalculationRelation->setMarkUsed($markUsed);
        $markAnnualCourseSequenceCalculationRelation->setSequence($sequence);
        $markAnnualCourseSequenceCalculationRelation->setEvaluationPeriod($studentCourseRegistration->getEvaluationPeriod());
    }

    // Generer les relations de calcul dans la table intermediaire
    // Relations avec les notes calculees de sequence
    // finalMarkFormula = 1
    function generateAnnualCourseCalculationRelations1( ?float $mark, array $courseMarks, ClassProgram $classProgram,MarkAnnualCourseCalculated $markAnnualCourseCalculated,?Module $module){
        foreach ($courseMarks as $sequenceMark) {
            if ($sequenceMark) {
                $markUsed = $sequenceMark->getMark();
                $sequence = $sequenceMark->getSequence();
                $studentCourseRegistration = $sequenceMark->getStudentCourseRegistration();
                $this->generateAnnualCourseCalculationRelation1($sequence, $studentCourseRegistration, $classProgram, $module, $sequenceMark, $markAnnualCourseCalculated, $markUsed, $mark);
            }
        }
    }


    // Generer la relation de calcul dans la table intermediaire
    function generateAnnualCourseCalculationRelation2(EvaluationPeriod $evaluationPeriod, StudentCourseRegistration $studentCourseRegistration, ClassProgram $classProgram, ?Module $module, MarkPeriodCourseCalculated $markPeriodCourseCalculated, MarkAnnualCourseCalculated $markAnnualCourseCalculated, ?float $markUsed, ?float $markCalculated)
    {
        $markAnnualCoursePeriodCalculationRelation = new MarkAnnualCoursePeriodCalculationRelation();
        $this->annualMarkGenerationUtil->setMarkAnnual($markAnnualCoursePeriodCalculationRelation);
        $markAnnualCoursePeriodCalculationRelation->setStudentCourseRegistration($studentCourseRegistration);
        $markAnnualCoursePeriodCalculationRelation->setClassProgram($classProgram);
        $markAnnualCoursePeriodCalculationRelation->setModule($module);
        $markAnnualCoursePeriodCalculationRelation->setMarkPeriodCourseCalculated($markPeriodCourseCalculated);
        $markAnnualCoursePeriodCalculationRelation->setMarkAnnualCourseCalculated($markAnnualCourseCalculated);
        $markAnnualCoursePeriodCalculationRelation->setMarkCalculated($markCalculated);
        $markAnnualCoursePeriodCalculationRelation->setMarkUsed($markUsed);
        $markAnnualCoursePeriodCalculationRelation->setEvaluationPeriod($evaluationPeriod);
    }

    // Generer les relations de calcul dans la table intermediaire
    // Relations avec les notes calculees de periode
    // finalMarkFormula = 2
    function generateAnnualCourseCalculationRelations2(?float $mark, array $courseMarks, ClassProgram $classProgram,MarkAnnualCourseCalculated $markAnnualCourseCalculated,?Module $module){
        foreach ($courseMarks as $periodMark) {
            if ($periodMark) {
                $markUsed = $periodMark->getMark();
                $evaluationPeriod = $periodMark->getEvaluationPeriod();
                $studentCourseRegistration = $periodMark->getStudentCourseRegistration();
                $this->generateAnnualCourseCalculationRelation2($evaluationPeriod, $studentCourseRegistration, $classProgram, $module, $periodMark, $markAnnualCourseCalculated, $markUsed, $mark);
            }
        }
    }
}