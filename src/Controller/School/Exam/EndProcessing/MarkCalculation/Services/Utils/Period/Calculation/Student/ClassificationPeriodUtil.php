<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Calculation\Student;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\ClassificationUtil;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;

class ClassificationPeriodUtil extends ClassificationUtil
{

    public function __construct(bool $calculateAveragesForUnclassified, float $percentageSubjectNumber, string $andOr, float $percentageTotalCoefficicient,
        // Attributs principaux.
                                protected readonly array $sequences,

        // Repository
                                protected readonly MarkSequenceCourseCalculatedRepository $markSequenceCourseCalculatedRepository,
    )
    {
        parent::__construct($calculateAveragesForUnclassified, $percentageSubjectNumber, $andOr, $percentageTotalCoefficicient);
    }

    function isComposed(StudentCourseRegistration $studentCourseRegistration): bool
    {
        foreach ($this->sequences as $sequence) {
            $courseMark = $this->markSequenceCourseCalculatedRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration,'sequence'=>$sequence]);
             if ($courseMark && $courseMark->getMark() !== null)  continue;
             else return false;
        }

        return true;
    }

}