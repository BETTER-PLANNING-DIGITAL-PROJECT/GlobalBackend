<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculated;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\School\Study\Program\ClassProgram;
use Closure;

class GeneralUtil
{
    public function __construct()
    {
    }

    static function calculateWeightingAverage(array $marks, bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation, bool $assign0WhenTheMarkIsNotEntered, bool $activateEliminationMarks): float|null
    {
        // Considerons les notes null null 5 avec note eliminatoire 7

        // On retire les notes eliminatoires car ni leur credits ni leurs notes ne sont pas considerer qu'importe les conditions
        if ($activateEliminationMarks) {
            $marks = array_filter($marks, fn(array $marks) => !$marks['eliminated']);
        }

        if (empty($marks)) { return null;}

        $notNullMarks = array_filter($marks, fn(array $markData) => isset($markData['mark']));

        // Maintenant si toutes les notes restantes sont nulles => moyenne nulle
        if (empty($notNullMarks)) {
            return !$assign0WhenTheMarkIsNotEntered || !$isCoefficientOfNullMarkConsiderInTheAverageCalculation ? null : 0;
        }
        else if (count($notNullMarks) != count($marks) && !$assign0WhenTheMarkIsNotEntered){
            return null;
        }

        $totalCredit = array_sum(array_column($isCoefficientOfNullMarkConsiderInTheAverageCalculation ? $marks : $notNullMarks, 'credit'));

        $marksWeighted = array_map(function (array $markData) {
            return floatval($markData['mark']) * $markData['credit'];
        }, $marks);
        $total = array_sum($marksWeighted);
//        dd($total,$totalCredit);
        return round(floatval($total / $totalCredit), 4);
    }

    // Calculer la note d'une matiere d'une sequence en considerant les type de notes
    function calculateSequenceCourseMeanAverage (array $marks, bool $assign0WhenTheMarkIsNotEntered): ?float {
        // Considerons les notes null null 5 avec note eliminatoire 7

        // On ne retire rien car on veut calculer la note d'une matiere sur une sequence

        $notNullMarks = array_filter($marks, fn(?float $mark) => isset($mark));

        // Maintenant si toutes les notes restantes sont nulles => moyenne nulle
        if (empty($notNullMarks)) {
            return !$assign0WhenTheMarkIsNotEntered ? null : 0;
        }
        else if (count($notNullMarks) != count($marks) && !$assign0WhenTheMarkIsNotEntered){
            return null;
        }

        $totalCredit = count($marks);

        $total = array_sum($marks);
//        dd($total,$totalCredit);
        return round(floatval($total / $totalCredit), 4);
//        return GeneralUtil::calculateWeightingAverage($marks, true, $this->assign0WhenTheMarkIsNotEntered, false);
    }

    function isClassedForSequenceWithConfigurations(StudentRegistration $student, EvaluationPeriod $evaluationPeriod, Sequence $sequence, bool $assign0WhenTheMarkIsNotEntered, bool $andOr, float $percentageSubjectNumber, float $percentageTotalCoefficicient)
    {
        $attendedGeneratedClassProgramsIsEnteredForSequence = $this->getAttendedStudentSequenceGeneratedClassPrograms($student, $evaluationPeriod, $sequence);

        $composedClassProgramsIsEnteredForSequence = $assign0WhenTheMarkIsNotEntered ? $attendedGeneratedClassProgramsIsEnteredForSequence : $this->getComposedClassPrograms($attendedGeneratedClassProgramsIsEnteredForSequence);

        $totalOfCredits = function (array $classProgramsIsEnteredForSequence) {
            return array_sum(array_map(
                function (ClassProgram $classProgram) {
                    return $classProgram->getCoeff();
                }, array_column($classProgramsIsEnteredForSequence, 'classProgram')));
        };

        return $this->isClassed($attendedGeneratedClassProgramsIsEnteredForSequence, $composedClassProgramsIsEnteredForSequence, $totalOfCredits, $percentageSubjectNumber, $andOr, $percentageTotalCoefficicient);
    }

    // Determiner si un etudiant est classee ou non (Fonction secondaire)
    function isClassed(array $studentCourseRegistrations, array $markCourseCalculateds, \Closure $totalOfCreditsAttendedFn, \Closure $totalOfCreditsComposedFn, float $percentageSubjectNumber, string $andOr, float $percentageTotalCoefficicient): bool
    {
        $numberOfAttendedClassPrograms = count($studentCourseRegistrations);
        $numberOfComposedClassPrograms = count($markCourseCalculateds);

        $totalOfCreditsAttended = $totalOfCreditsAttendedFn($studentCourseRegistrations);
        $totalOfCreditsComposed = $totalOfCreditsComposedFn($markCourseCalculateds);

        $isClassed = ($numberOfComposedClassPrograms / $numberOfAttendedClassPrograms) < ($percentageSubjectNumber / 100);

        if ($andOr === 'a') $isClassed = $isClassed && ($totalOfCreditsComposed / $totalOfCreditsAttended) < ($percentageTotalCoefficicient / 100);
        if ($andOr === 'o') $isClassed = $isClassed || ($totalOfCreditsComposed / $totalOfCreditsAttended) < ($percentageTotalCoefficicient / 100);

        return !$isClassed;
    }

    // Calcul de la note de periode d'une matiere
    // halfYearAverageFormula = 1
    // assign0WhenTheMarkIsNotEntered = true | false
    // $isMarkForAllSequenceRequired = true | false
    function calculatePeriodCourseMark1(array $markDatas,bool $assign0WhenTheMarkIsNotEntered,bool $isMarkForAllSequenceRequired)
    {
        // Verification
        // Recuperer les notes (markDatas)
        $numberOfMarks = count($markDatas);

        // Recuperer les notes generees
        $markDatas = array_values(array_filter($markDatas, fn(array $markData) => $markData['markSequenceCourseCalculated']));
        $numberOfMarksGenerated = count($markDatas);

        // S'il y a une note non generee
        if ($numberOfMarksGenerated !== $numberOfMarks
            // Si $isMarkForAllSequenceRequired = true , renvoyer null
            && $isMarkForAllSequenceRequired) return null;
        // Sinon retirer cette note
        // On a deja retire

        // Recuperer les notes saisies
        $markDatas = array_values(array_filter($markDatas, fn(array $markData) => $markData['markSequenceCourseCalculated']->getMark()));
        $numberOfMarksEntered = count($markDatas);

        // S'il y a une note non saisie
        if ($numberOfMarksGenerated !== $numberOfMarksEntered
            // Si $assign0WhenTheMarkIsNotEntered = false , renvoyer null
            && $isMarkForAllSequenceRequired && !$assign0WhenTheMarkIsNotEntered) return null;
        // Sinon continuer, on manage ces cas plus tard


        // Calculer la note suivant la formule
        $sum = $totalCredit =0;
        foreach ($markDatas as $markData) {
            $mark = $markData['markSequenceCourseCalculated']->getMark();
            $credit = $markData['credit'];
            $sum += floatval($mark) * $credit;
            $totalCredit += $credit;
        }
        $mark = round(floatval($sum / $totalCredit),2);
        return $mark;
    }

    // halfYearAverageFormula = 2
    // assign0WhenTheMarkIsNotEntered = true | false
    // $isMarkForAllSequenceRequired = true | false
    function calculatePeriodCourseMark2(array $markSequenceCourseCalculateds,bool $assign0WhenTheMarkIsNotEntered,bool $isMarkForAllSequenceRequired)
    {
        // Verification
        // Recuperer les notes ($markSequenceCourseCalculateds)
        $numberOfMarks = count($markSequenceCourseCalculateds);

        // Recuperer les notes generees
        $markSequenceCourseCalculatedsGenerated = [];
        foreach ($markSequenceCourseCalculateds as $markSequenceCourseCalculated) {
            if ($markSequenceCourseCalculated) $markSequenceCourseCalculatedsGenerated[] = $markSequenceCourseCalculated;
        }
        $numberOfMarksGenerated = count($markSequenceCourseCalculatedsGenerated);

        // S'il y a une note non generee
        if ($numberOfMarksGenerated !== $numberOfMarks
            // Si $isMarkForAllSequenceRequired = true , renvoyer null
            && $isMarkForAllSequenceRequired) return null;
        // Sinon retirer cette note
        // On a deja retire

        // Recuperer les notes saisies
        $marks = array_map(fn(MarkSequenceCourseCalculated $markSequenceCourseCalculated)=>$markSequenceCourseCalculated->getMark(),$markSequenceCourseCalculatedsGenerated);
        $numberOfMarksEntered = count($marks);

        // S'il y a une note non saisie
        if ($numberOfMarksGenerated !== $numberOfMarksEntered
            // Si $assign0WhenTheMarkIsNotEntered = false , renvoyer null
            && $isMarkForAllSequenceRequired && !$assign0WhenTheMarkIsNotEntered) return null;
        // Sinon continuer, on manage ces cas plus tard

        // Calculer la note suivant la formule
        $sum = 0;
        foreach ($marks as $mark) {
            $sum += floatval($mark);
        }
        $mark = round(floatval($sum / $numberOfMarksEntered),2);
        return $mark;
    }


}