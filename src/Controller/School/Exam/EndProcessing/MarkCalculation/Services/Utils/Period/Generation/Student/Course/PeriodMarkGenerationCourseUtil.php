<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Generation\Student\Course;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Generation\Student\PeriodMarkGenerationUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Generation\Student\GeneralAverage\PeriodMarkGenerationGeneralAverageUtil;
use App\Entity\School\Exam\Configuration\NoteType;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Mark;
use App\Entity\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculated;
use App\Entity\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculationRelation;
use App\Entity\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculated;
use App\Entity\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculationRelation;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Study\Configuration\Module;
use App\Entity\School\Study\Program\ClassProgram;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;

// Classe contenant toutes les fonctions concernant la generation des notes de matiere pour une sequence
class PeriodMarkGenerationCourseUtil
{
    public function __construct(
        // Repository
        private EntityManagerInterface   $entityManager,

        // Attributs principaux
        private PeriodMarkGenerationUtil $periodMarkGenerationUtil,
    )
    {

    }

    public function getPeriodMarkGenerationUtil(): PeriodMarkGenerationUtil
    {
        return $this->periodMarkGenerationUtil;
    }

    public function setPeriodMarkGenerationUtil(PeriodMarkGenerationUtil $periodMarkGenerationUtil): PeriodMarkGenerationCourseUtil
    {
        $this->periodMarkGenerationUtil = $periodMarkGenerationUtil;
        return $this;
    }

    // Generer la note d'une matieres d'une sequence
    // On retourne l'elmt ajoute pour ajouter l'attribut isEliminated s'il y a les eliminations
    function generatePeriodCourseCalculated(StudentCourseRegistration $studentCourseRegistration, ?float $mark, string $isCourseValidated, array $sequenceMarks, ClassProgram $classProgram, ?float $validationBase, ?float $coeff): MarkPeriodCourseCalculated
    {

        $markPeriodCourseCalculated = new MarkPeriodCourseCalculated();
        $this->periodMarkGenerationUtil->setMarkPeriod($markPeriodCourseCalculated);
        $markPeriodCourseCalculated->setStudentCourseRegistration($studentCourseRegistration);
        $markPeriodCourseCalculated->setMark($mark);

        $markPeriodCourseCalculated->setClassProgram($classProgram);
        $module = $studentCourseRegistration->getModule();
        $markPeriodCourseCalculated->setModule($module);
        $markPeriodCourseCalculated->setIsCourseValidated($isCourseValidated);
        $markPeriodCourseCalculated->setValidationBase($validationBase);
        $markPeriodCourseCalculated->setCoeff($coeff);

        $markPeriodCourseCalculated->setTotal(isset($mark) ? $mark * $coeff : null);
        $this->periodMarkGenerationUtil->setMarkGrade($markPeriodCourseCalculated, $mark);
        foreach ($sequenceMarks as $sequenceMark) {
            if ($sequenceMark) {
                $markUsed = $sequenceMark->getMark();
                $sequence = $sequenceMark->getSequence();
                $this->generatePeriodCourseCalculationRelation($sequence,$studentCourseRegistration, $classProgram, $module, $sequenceMark, $markPeriodCourseCalculated, $markUsed, $mark);
            }
        }
        $this->entityManager->flush();
        return $markPeriodCourseCalculated;
    }


    // Generer la relation de calcul dans la table intermediaire
    function generatePeriodCourseCalculationRelation(Sequence $sequence, StudentCourseRegistration $studentCourseRegistration, ClassProgram $classProgram, ?Module $module, MarkSequenceCourseCalculated $markSequenceCourseCalculated, MarkPeriodCourseCalculated $markPeriodCourseCalculated, ?float $markUsed, ?float $markCalculated)
    {
        $markPeriodCourseCalculationRelation = new MarkPeriodCourseCalculationRelation();
        $this->periodMarkGenerationUtil->setMarkPeriod($markPeriodCourseCalculationRelation);
        $markPeriodCourseCalculationRelation->setStudentCourseRegistration($studentCourseRegistration);
        $markPeriodCourseCalculationRelation->setClassProgram($classProgram);
        $markPeriodCourseCalculationRelation->setModule($module);
        $markPeriodCourseCalculationRelation->setMarkSequenceCourseCalculated($markSequenceCourseCalculated);
        $markPeriodCourseCalculationRelation->setMarkPeriodCourseCalculated($markPeriodCourseCalculated);
        $markPeriodCourseCalculationRelation->setMarkCalculated($markCalculated);
        $markPeriodCourseCalculationRelation->setMarkUsed($markUsed);
        $markPeriodCourseCalculationRelation->setSequence($sequence);
    }
}