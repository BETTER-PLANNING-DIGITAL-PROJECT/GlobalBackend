<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Generation\Student;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\GetConfigurationsUtil;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;

// Classe contenant toutes les fonctions concernant la generation des notes (calculees/relation)
class SequenceMarkGenerationUtil
{
    private StudentRegistration $student;

    public function __construct(
        // Configurations
        private readonly GetConfigurationsUtil $configurationsUtil,

        // Attributs principaux
        private Institution                     $institution,
        private Year                            $year,
        private School                          $school,
        private SchoolClass                     $class,
        private EvaluationPeriod                $evaluationPeriod,
        private Sequence                        $sequence,
        private User                            $user,

        // Repository
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    // Affecter les valeurs communes pour toutes les classes MarkSequence*
    function setMarkSequence(mixed $markSequence): void
    {
        $markSequence->setInstitution($this->institution);
        $markSequence->setYear($this->year);
        $markSequence->setSchool($this->school);
        $markSequence->setClass($this->class);
        $markSequence->setStudent($this->student);
        $markSequence->setEvaluationPeriod($this->evaluationPeriod);
        $markSequence->setSequence($this->sequence);
        $markSequence->setUser($this->user);

        $this->entityManager->persist($markSequence);
    }

    function setMarkGrade(mixed $markSequenceCourseCalculated,?float $mark): void
    {
        $markGrade = $this->configurationsUtil->getMarkGrade($this->school,$mark);
        $markSequenceCourseCalculated->setGrade($markGrade);
    }

    public function getInstitution(): Institution
    {
        return $this->institution;
    }

    public function setInstitution(Institution $institution): SequenceMarkGenerationUtil
    {
        $this->institution = $institution;
        return $this;
    }

    public function getYear(): Year
    {
        return $this->year;
    }

    public function setYear(Year $year): SequenceMarkGenerationUtil
    {
        $this->year = $year;
        return $this;
    }

    public function getSchool(): School
    {
        return $this->school;
    }

    public function setSchool(School $school): SequenceMarkGenerationUtil
    {
        $this->school = $school;
        return $this;
    }

    public function getClass(): SchoolClass
    {
        return $this->class;
    }

    public function setClass(SchoolClass $class): SequenceMarkGenerationUtil
    {
        $this->class = $class;
        return $this;
    }

    public function getStudent(): StudentRegistration
    {
        return $this->student;
    }

    public function setStudent(StudentRegistration $student): SequenceMarkGenerationUtil
    {
        $this->student = $student;
        return $this;
    }

    public function getEvaluationPeriod(): EvaluationPeriod
    {
        return $this->evaluationPeriod;
    }

    public function setEvaluationPeriod(EvaluationPeriod $evaluationPeriod): SequenceMarkGenerationUtil
    {
        $this->evaluationPeriod = $evaluationPeriod;
        return $this;
    }

    public function getSequence(): Sequence
    {
        return $this->sequence;
    }

    public function setSequence(Sequence $sequence): SequenceMarkGenerationUtil
    {
        $this->sequence = $sequence;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): SequenceMarkGenerationUtil
    {
        $this->user = $user;
        return $this;
    }
}