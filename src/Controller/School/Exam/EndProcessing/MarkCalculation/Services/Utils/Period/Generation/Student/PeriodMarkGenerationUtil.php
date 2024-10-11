<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Generation\Student;

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
class PeriodMarkGenerationUtil
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
        private User                            $user,

        // Repository
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    // Affecter les valeurs communes pour toutes les classes MarkSequence*
    function setMarkPeriod(mixed $markSequence): void
    {
        $markSequence->setInstitution($this->institution);
        $markSequence->setYear($this->year);
        $markSequence->setSchool($this->school);
        $markSequence->setClass($this->class);
        $markSequence->setStudent($this->student);
        $markSequence->setEvaluationPeriod($this->evaluationPeriod);
        $markSequence->setUser($this->user);

        $this->entityManager->persist($markSequence);
    }

    function setMarkGrade(mixed $markPeriodCourseCalculated,?float $mark): void
    {
        $markGrade = $this->configurationsUtil->getMarkGrade($this->school,$mark);
        $markPeriodCourseCalculated->setGrade($markGrade);
    }

    public function getInstitution(): Institution
    {
        return $this->institution;
    }

    public function setInstitution(Institution $institution): PeriodMarkGenerationUtil
    {
        $this->institution = $institution;
        return $this;
    }

    public function getYear(): Year
    {
        return $this->year;
    }

    public function setYear(Year $year): PeriodMarkGenerationUtil
    {
        $this->year = $year;
        return $this;
    }

    public function getSchool(): School
    {
        return $this->school;
    }

    public function setSchool(School $school): PeriodMarkGenerationUtil
    {
        $this->school = $school;
        return $this;
    }

    public function getClass(): SchoolClass
    {
        return $this->class;
    }

    public function setClass(SchoolClass $class): PeriodMarkGenerationUtil
    {
        $this->class = $class;
        return $this;
    }

    public function getStudent(): StudentRegistration
    {
        return $this->student;
    }

    public function setStudent(StudentRegistration $student): PeriodMarkGenerationUtil
    {
        $this->student = $student;
        return $this;
    }

    public function getEvaluationPeriod(): EvaluationPeriod
    {
        return $this->evaluationPeriod;
    }

    public function setEvaluationPeriod(EvaluationPeriod $evaluationPeriod): PeriodMarkGenerationUtil
    {
        $this->evaluationPeriod = $evaluationPeriod;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): PeriodMarkGenerationUtil
    {
        $this->user = $user;
        return $this;
    }
}