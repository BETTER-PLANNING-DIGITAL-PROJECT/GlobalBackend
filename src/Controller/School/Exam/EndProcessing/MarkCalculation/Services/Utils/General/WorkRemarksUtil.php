<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General;

use App\Entity\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculated;
use App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculated;

class WorkRemarksUtil
{

    protected array $goodRemarksData = [];
    protected array $badRemarksData = [];

    protected string $setRemarksMethod;

    public function __construct(
        // Remarques de l'evaluation (Encouragements , Blames , Felicitations etc...)
        protected ?float $blameWorkAverage,
        protected ?float $warningWorkAverage,
        protected ?float $grantEncouragementAverage,
        protected ?float $grantCongratulationAverage,
    )
    {
        if(isset($this->blameWorkAverage)) $this->badRemarksData[] = ['setterAttribute'=>'BlameWork','average'=>$this->blameWorkAverage];
        if(isset($this->warningWorkAverage)) $this->badRemarksData[] = ['setterAttribute'=>'WarningWork','average'=>$this->warningWorkAverage];

        if(isset($this->grantEncouragementAverage)) $this->goodRemarksData[] = ['setterAttribute'=>'GrantEncouragement','average'=>$this->grantEncouragementAverage];
        if(isset($this->grantCongratulationAverage)) $this->goodRemarksData[] = ['setterAttribute'=>'GrantCongratulation','average'=>$this->grantCongratulationAverage];

    }
    
    function setRemarks(mixed $markSequenceGeneralAverageCalculated): ?float{

        // Configurations de la formule Th pour les reports (Encouragements , Blames , etc...)
        $average = $markSequenceGeneralAverageCalculated->getAverage();
        if (isset($average)) {
            $this->{$this->setRemarksMethod}($markSequenceGeneralAverageCalculated,$average);
        }
        return $average;
        /*
        Methode generale
        if ($this->hasBadRemarks && isset($average)) {
            foreach ($this->badRemarksData as $badRemarksItem) {
                if ($average < $badRemarksItem['average']) {
                    $markSequenceGeneralAverageCalculated->{'set'.$badRemarksItem['setterAttribute']}(true);
                    break;
                }
            }
        }

        if ($this->hasGoodRemarks && isset($average)) {
            foreach ($this->goodRemarksData as $goodRemarksItem) {
                if ($average >= $goodRemarksItem['average']) {
                    $markSequenceGeneralAverageCalculated->{'set'.$goodRemarksItem['setterAttribute']}(true);
                    break;
                }
            }
        }*/
    }

    // hasBadRemarks = 0
    // hasGoodRemarks = 0
    function setRemarksBad0Good0(mixed $markSequenceGeneralAverageCalculated,float $average){
        // On ne fait rien les deux sont faux
        // On cree cette methode pour eviter de regarder si les deux sont faux avant de mettre les remarques
    }

    // hasBadRemarks = 0
    // hasGoodRemarks = 1
    function setRemarksBad0Good1(mixed $markSequenceGeneralAverageCalculated,float $average)
    {
            foreach ($this->goodRemarksData as $goodRemarksItem) {
                if ($average >= $goodRemarksItem['average']) {
                    $markSequenceGeneralAverageCalculated->{'set'.$goodRemarksItem['setterAttribute']}(true);
                    break;
                }
            }
    }

    // hasBadRemarks = 1
    // hasGoodRemarks = 0
    function setRemarksBad1Good0(mixed $markSequenceGeneralAverageCalculated,float $average)
    {
            foreach ($this->badRemarksData as $badRemarksItem) {
                if ($average < $badRemarksItem['average']) {
                    $markSequenceGeneralAverageCalculated->{'set'.$badRemarksItem['setterAttribute']}(true);
                    break;
                }
            }
    }

    // hasBadRemarks = 1
    // hasGoodRemarks = 1
    function setRemarksBad1Good1(mixed $markSequenceGeneralAverageCalculated,float $average)
    {
            foreach ($this->badRemarksData as $badRemarksItem) {
                if ($average < $badRemarksItem['average']) {
                    $markSequenceGeneralAverageCalculated->{'set'.$badRemarksItem['setterAttribute']}(true);
                    break;
                }
            }

            foreach ($this->goodRemarksData as $goodRemarksItem) {
                if ($average >= $goodRemarksItem['average']) {
                    $markSequenceGeneralAverageCalculated->{'set'.$goodRemarksItem['setterAttribute']}(true);
                    break;
                }
            }
    }

}