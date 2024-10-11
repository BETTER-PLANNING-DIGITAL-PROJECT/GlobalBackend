<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\ClassificationUtil;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;

class ClassificationAnnualUtil extends ClassificationUtil
{

    public function __construct(bool $calculateAveragesForUnclassified, float $percentageSubjectNumber, string $andOr, float $percentageTotalCoefficicient,
        // Repository
                                protected readonly MarkPeriodCourseCalculatedRepository $markPeriodCourseCalculatedRepository,
    )
    {
        parent::__construct($calculateAveragesForUnclassified, $percentageSubjectNumber, $andOr, $percentageTotalCoefficicient);
    }

    function isComposed(StudentCourseRegistration $studentCourseRegistration): bool
    {
        $markPeriodCourseCalculated = $this->markPeriodCourseCalculatedRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration]);

        return isset($markPeriodCourseCalculated);
    }

}