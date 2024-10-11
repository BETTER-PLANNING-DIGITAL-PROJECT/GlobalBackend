<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\WorkRemarksUtil;
use App\Entity\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculated;
use App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculated;

class WorkRemarksSequenceUtil extends WorkRemarksUtil
{

    public function __construct(
        // Remarques de l'evaluation (Encouragements , Blames , Felicitations etc...)
        protected ?float $blameWorkAverage,
        protected ?float $warningWorkAverage,
        protected ?float $grantEncouragementAverage,
        protected ?float $grantCongratulationAverage
    )
    {
        parent::__construct($this->blameWorkAverage,$this->warningWorkAverage,$this->grantEncouragementAverage,$this->grantCongratulationAverage);
        $hasBadRemarks = count($this->badRemarksData) > 0;
        $hasGoodRemarks = count($this->goodRemarksData) > 0;
        $this->setRemarksMethod = 'setRemarksBad'.(int)$hasBadRemarks.'Good'.(int)$hasGoodRemarks;
    }
}