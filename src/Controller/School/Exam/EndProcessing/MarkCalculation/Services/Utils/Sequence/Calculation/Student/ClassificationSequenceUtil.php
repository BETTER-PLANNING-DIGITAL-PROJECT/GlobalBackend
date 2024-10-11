<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\ClassificationUtil;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Repository\School\Exam\Operation\MarkRepository;

class ClassificationSequenceUtil extends ClassificationUtil
{

    public function __construct(bool $calculateAveragesForUnclassified, float $percentageSubjectNumber, string $andOr, float $percentageTotalCoefficicient,
        // Attributs principaux.
                                protected readonly Sequence $sequence,

        // Repository
                                protected readonly MarkRepository $markRepository
    )
    {
        parent::__construct($calculateAveragesForUnclassified, $percentageSubjectNumber, $andOr, $percentageTotalCoefficicient);
    }

    function isComposed(StudentCourseRegistration $studentCourseRegistration): bool
    {
        $courseMarks = $this->markRepository->findBy(['studentCourseRegistration' => $studentCourseRegistration,'sequence'=>$this->sequence]);
        if (empty($courseMarks)) return false;

        foreach ($courseMarks as $courseMark){
            if (!$courseMark->getMark()) return false;
        }
        return true;
    }

}