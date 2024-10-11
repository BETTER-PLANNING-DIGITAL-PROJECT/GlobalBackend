<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General;

use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculated;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Repository\School\Exam\Operation\MarkRepository;

abstract class ClassificationUtil
{
    protected bool $isEveryBodyClassed;

    public function __construct(
        // Configurations
            protected readonly bool $calculateAveragesForUnclassified,
            protected readonly float $percentageSubjectNumber,
            protected readonly string $andOr,
            protected readonly float $percentageTotalCoefficicient,
    )
    {
        $this->isEveryBodyClassed = $this->calculateAveragesForUnclassified || ($this->andOr === 'o' ? ($this->percentageTotalCoefficicient === 0 || $this->percentageSubjectNumber === 0) : (($this->percentageTotalCoefficicient === 0 && $this->percentageSubjectNumber === 0)));
    }

    public function isEveryBodyClassed(): bool
    {
        return $this->isEveryBodyClassed;
    }

    public function setIsEveryBodyClassed(bool $isEveryBodyClassed): ClassificationUtil
    {
        $this->isEveryBodyClassed = $isEveryBodyClassed;
        return $this;
    }


    abstract function isComposed(StudentCourseRegistration $studentCourseRegistration): bool;

    function isClassedWithCourses(array $studentCourseRegistrationsAttended){
        $totalOfCreditsAttended = 0;
        $totalOfCreditsComposed = 0;
        $numberOfAttendedCourses = 0;
        $numberOfComposedCourses = 0;

        foreach ($studentCourseRegistrationsAttended as $studentCourseRegistration) {
            $coeff = $studentCourseRegistration->getClassProgram()->getCoeff();
            if ($this->isComposed($studentCourseRegistration)){
                $numberOfComposedCourses++;
                $totalOfCreditsComposed += $coeff;
            }
            $numberOfAttendedCourses++;
            $totalOfCreditsAttended += $coeff;
        }

        $classificationDatas = $this->isClassed($numberOfAttendedCourses, $numberOfComposedCourses, $totalOfCreditsAttended, $totalOfCreditsComposed);
        $isClassed = $classificationDatas['isClassed'];
        $percentageSubjectNumber = $classificationDatas['percentageSubjectNumber'];
        $percentageTotalCoefficient = $classificationDatas['percentageTotalCoefficient'];

        return ['isClassed'=>$isClassed,
            'numberOfAttendedCourses'=>$numberOfAttendedCourses,
            'numberOfComposedCourses'=>$numberOfComposedCourses
            ,'totalOfCreditsAttended'=>$totalOfCreditsAttended
            ,'totalOfCreditsComposed'=>$totalOfCreditsComposed,
            'percentageSubjectNumber'=>$percentageSubjectNumber,
            'percentageTotalCoefficient'=>$percentageTotalCoefficient
            ];
    }

    static function totalOfCreditsAttendedFn(array $studentCourseRegistrations)
    {
        return array_sum(array_map(fn(mixed $studentCourseRegistration)=>$studentCourseRegistration->getClassProgram()->getCoeff(),$studentCourseRegistrations));
    }

    // Determiner si un etudiant est classee ou non (Fonction secondaire)
    function isClassed(int $numberOfAttendedCourses,int $numberOfComposedCourses,float $totalOfCreditsAttended,float $totalOfCreditsComposed): array
    {
        $isClassed = $this->{'isClassed' . $this->andOr}($numberOfAttendedCourses, $numberOfComposedCourses, $totalOfCreditsAttended, $totalOfCreditsComposed);
        return ['isClassed'=>$isClassed,'percentageSubjectNumber'=>round(($numberOfComposedCourses / $numberOfAttendedCourses)*100,2),'percentageTotalCoefficient'=>round(($totalOfCreditsComposed / $totalOfCreditsAttended)*100,2)];
    }

    // Determiner si un etudiant est classee ou non (Fonction secondaire)
    // $andOr = a
    function isClasseda(int $numberOfAttendedCourses,int $numberOfComposedCourses,float $totalOfCreditsAttended,float $totalOfCreditsComposed): bool
    {
       $isClassed = ($numberOfComposedCourses / $numberOfAttendedCourses) < ($this->percentageSubjectNumber / 100);

        $isClassed = $isClassed && ($totalOfCreditsComposed / $totalOfCreditsAttended) < ($this->percentageTotalCoefficicient / 100);

        return !$isClassed;
    }

    // Determiner si un etudiant est classee ou non (Fonction secondaire)
    // $andOr = o
    function isClassedo(int $numberOfAttendedCourses,int $numberOfComposedCourses,float $totalOfCreditsAttended,float $totalOfCreditsComposed): bool
    {
        $isClassed = ($numberOfComposedCourses / $numberOfAttendedCourses) < ($this->percentageSubjectNumber / 100);

        $isClassed = $isClassed || ($totalOfCreditsComposed / $totalOfCreditsAttended) < ($this->percentageTotalCoefficicient / 100);

        return !$isClassed;
    }
}