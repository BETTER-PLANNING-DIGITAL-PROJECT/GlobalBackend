<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Generation\Student;

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
class AnnualMarkGenerationUtil
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
        private User                            $user,

        // Repository
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    // Affecter les valeurs communes pour toutes les classes MarkSequence*
    function setMarkAnnual(mixed $markAnnual): void
    {
        $markAnnual->setInstitution($this->institution);
        $markAnnual->setYear($this->year);
        $markAnnual->setSchool($this->school);
        $markAnnual->setClass($this->class);
        $markAnnual->setStudent($this->student);
        $markAnnual->setUser($this->user);

        $this->entityManager->persist($markAnnual);
    }

    function setMarkGrade(mixed $markAnnualCourseCalculated,?float $mark): void
    {
        $markGrade = $this->configurationsUtil->getMarkGrade($this->school,$mark);
        $markAnnualCourseCalculated->setGrade($markGrade);
    }

    public function getInstitution(): Institution
    {
        return $this->institution;
    }

    public function setInstitution(Institution $institution): AnnualMarkGenerationUtil
    {
        $this->institution = $institution;
        return $this;
    }

    public function getYear(): Year
    {
        return $this->year;
    }

    public function setYear(Year $year): AnnualMarkGenerationUtil
    {
        $this->year = $year;
        return $this;
    }

    public function getSchool(): School
    {
        return $this->school;
    }

    public function setSchool(School $school): AnnualMarkGenerationUtil
    {
        $this->school = $school;
        return $this;
    }

    public function getClass(): SchoolClass
    {
        return $this->class;
    }

    public function setClass(SchoolClass $class): AnnualMarkGenerationUtil
    {
        $this->class = $class;
        return $this;
    }

    public function getStudent(): StudentRegistration
    {
        return $this->student;
    }

    public function setStudent(StudentRegistration $student): AnnualMarkGenerationUtil
    {
        $this->student = $student;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): AnnualMarkGenerationUtil
    {
        $this->user = $user;
        return $this;
    }
}