<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Generation\Student\Course;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Generation\Student\GeneralAverage\SequenceMarkGenerationGeneralAverageUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Generation\Student\SequenceMarkGenerationUtil;
use App\Entity\School\Exam\Configuration\NoteType;
use App\Entity\School\Exam\Operation\Mark;
use App\Entity\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculated;
use App\Entity\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculationRelation;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Study\Program\ClassProgram;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;

// Classe contenant toutes les fonctions concernant la generation des notes de matiere pour une sequence
class SequenceMarkGenerationCourseUtil
{
    public function __construct(
        // Repository
        private EntityManagerInterface   $entityManager,

        // Attributs principaux
        private SequenceMarkGenerationUtil   $sequenceMarkGenerationUtil,
    )
    {

    }

    public function getSequenceMarkGenerationUtil(): SequenceMarkGenerationUtil
    {
        return $this->sequenceMarkGenerationUtil;
    }

    public function setSequenceMarkGenerationUtil(SequenceMarkGenerationUtil $sequenceMarkGenerationUtil): SequenceMarkGenerationCourseUtil
    {
        $this->sequenceMarkGenerationUtil = $sequenceMarkGenerationUtil;
        return $this;
    }

    // Generer la note d'une matieres d'une sequence
    // On retourne l'elmt ajoute pour ajouter l'attribut isEliminated s'il y a les eliminations
    function generateSequenceCourseCalculated(StudentCourseRegistration $studentCourseRegistration, ?float $mark, bool $isCourseValidated,array $sequenceMarks,ClassProgram $classProgram,?float $eliminateMark,?float $validationBase,?float $coeff): MarkSequenceCourseCalculated
    {

        $markSequenceCourseCalculated = new MarkSequenceCourseCalculated();
        $this->sequenceMarkGenerationUtil->setMarkSequence($markSequenceCourseCalculated);
        $markSequenceCourseCalculated->setStudentCourseRegistration($studentCourseRegistration);
        $markSequenceCourseCalculated->setMark($mark);

        $markSequenceCourseCalculated->setClassProgram($classProgram);
        $module = $studentCourseRegistration->getModule();
        $markSequenceCourseCalculated->setModule($module);
        $markSequenceCourseCalculated->setIsCourseValidated($isCourseValidated);
        $markSequenceCourseCalculated->setEliminateMark($eliminateMark);
        $markSequenceCourseCalculated->setValidationBase($validationBase);
        $markSequenceCourseCalculated->setCoeff($coeff);
        $markSequenceCourseCalculated->setTotal(isset($mark) ? $mark * $coeff : null);


        $this->sequenceMarkGenerationUtil->setMarkGrade($markSequenceCourseCalculated, $mark);
        // ,ClassProgram $classProgram,NoteType $noteType,Mark $sequenceMark,MarkSequenceCourseCalculated $markSequenceCourseCalculated
        foreach ($sequenceMarks as $sequenceMark) {
            $noteType = $sequenceMark->getNoteType();
            $markUsed = $sequenceMark->getMark();
            $this->generateSequenceCourseCalculationRelation($studentCourseRegistration, $classProgram, $noteType, $sequenceMark, $markSequenceCourseCalculated,$markUsed,$mark);
        }
        $this->entityManager->flush();
        return $markSequenceCourseCalculated;
    }

    // Generer la note d'une matiere d'une sequence lorsqu'il y a des eliminations
    function generateSequenceCourseCalculatedElimination(StudentCourseRegistration $studentCourseRegistration, ?float $mark, int $base, bool $isCourseValidated, bool $isEliminated,array $sequenceMarks,ClassProgram $classProgram,?float $eliminateMark,?float $validationBase,?float $coeff): MarkSequenceCourseCalculated
    {
        $markSequenceCourseCalculated = $this->generateSequenceCourseCalculated($studentCourseRegistration, $mark, $base, $isCourseValidated,$sequenceMarks,$classProgram, $eliminateMark,$validationBase, $coeff);
        $markSequenceCourseCalculated->setIsEliminated($isEliminated);
        return $markSequenceCourseCalculated;
    }

    // Generer la relation de calcul dans la table intermediaire
    function generateSequenceCourseCalculationRelation(StudentCourseRegistration $studentCourseRegistration, ClassProgram $classProgram, ?NoteType $noteType, Mark $sequenceMark, MarkSequenceCourseCalculated $markSequenceCourseCalculated, ?float $markUsed, ?float $markCalculated)
    {
        $markSequenceCourseCalculationRelation = new MarkSequenceCourseCalculationRelation();
        $this->sequenceMarkGenerationUtil->setMarkSequence($markSequenceCourseCalculationRelation);
        $markSequenceCourseCalculationRelation->setStudentCourseRegistration($studentCourseRegistration);
        $markSequenceCourseCalculationRelation->setClassProgram($classProgram);
        $markSequenceCourseCalculationRelation->setNoteType($noteType);
        $markSequenceCourseCalculationRelation->setSequenceMark($sequenceMark);
        $markSequenceCourseCalculationRelation->setMarkSequenceCourseCalculated($markSequenceCourseCalculated);
        $markSequenceCourseCalculationRelation->setMarkUsed($markUsed);
        $markSequenceCourseCalculationRelation->setMarkCalculated($markCalculated);
    }
}