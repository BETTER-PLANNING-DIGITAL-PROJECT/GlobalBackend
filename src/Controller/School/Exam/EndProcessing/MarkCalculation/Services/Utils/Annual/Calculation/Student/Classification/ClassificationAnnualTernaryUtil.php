<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Classification;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\ClassificationUtil;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Repository\School\Exam\Operation\Annual\Course\MarkAnnualCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculatedRepository;

class ClassificationAnnualTernaryUtil extends ClassificationUtil
{

    public function __construct(bool                                                    $calculateAveragesForUnclassified, float $percentageSubjectNumber, string $andOr, float $percentageTotalCoefficicient,
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