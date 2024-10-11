<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\Course;

// Classe contenant toutes les fonctions concernant le calcul des notes pour une sequence pour les matieres
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\SequenceMarkCalculationUtil;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Mark;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;

// Il serait preferable de creer un sequence mark calculation pour chaque type (Matiere,Module,Moyenne generale)
class SequenceMarkCalculationCourseUtil extends SequenceMarkCalculationUtil
{
    private bool $isEliminationCourseActivated = false;

    public function __construct(
        // Configurations
        // Calc des notes d'une matiere
        protected bool $assign0WhenTheMarkIsNotEntered,

        // Attributs principaux
        private EvaluationPeriod $evaluationPeriod,
        private Sequence $sequence,

        // Repository
        private StudentCourseRegistrationRepository       $studentCourseRegistrationRepository,
        private readonly MarkRepository $markRepository,
    )
    {
        parent::__construct($this->assign0WhenTheMarkIsNotEntered);
        $this->calculateMethod = 'calculateSequenceCourseMeanAverage'.(int)($this->assign0WhenTheMarkIsNotEntered);
    }

    // Getters & setters
    public function isEliminationCourseActivated(): bool
    {
        return $this->isEliminationCourseActivated;
    }

    public function setIsEliminationCourseActivated(bool $isEliminationCourseActivated): SequenceMarkCalculationCourseUtil
    {
        $this->isEliminationCourseActivated = $isEliminationCourseActivated;
        return $this;
    }

    public function getCourses(StudentRegistration $student)
    {
        return $studentCourseRegistrations = $this->studentCourseRegistrationRepository->findBy(['evaluationPeriod' => $this->evaluationPeriod, 'StudRegistration' => $student]);
    }

    public function markExists(StudentCourseRegistration $studentCourseRegistration)
    {
        return $this->markRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $this->sequence]) !== null;
    }

    public function getMarks(StudentCourseRegistration $studentCourseRegistration)
    {
        return $studentCourseRegistrations = $this->markRepository->findBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $this->sequence]);
    }

    // calculateSequenceCourseMeanAverage($marks, bool $assign0WhenTheMarkIsNotEntered)
    // Calculer la note d'une matiere d'une sequence en considerant les type de notes
    // assign0WhenTheMarkIsNotEntered = false
    function calculateSequenceCourseMeanAverage0 (array $marks): ?float {
        // Considerons les notes null null 5 avec note eliminatoire 7

        // On ne retire rien car on veut calculer la note d'une matiere sur une sequence
//        $marks = array_map(fn(Mark $mark) => $mark->getMark(), $marks);
        $notNullMarks = array_filter($marks, fn(Mark $mark) => $mark->getMark() !== null);

        // Maintenant si toutes les notes restantes sont nulles => moyenne nulle
        if (empty($notNullMarks)) {
            return null;
        }
        else if (count($notNullMarks) != count($marks)){
            return null;
        }

        $totalCredit = 0;
        $total = 0;

        foreach ($marks as $marksObject) {
            $mark = $marksObject->getMark();
            $weighting = $marksObject->getWeighting() ?? 1;
            $total += $mark * $weighting;
            $totalCredit += $weighting;
        }
//        dd($total,$totalCredit);
        $mark = $totalCredit !== 0 ? round(floatval($total / $totalCredit), 4) : null;
        return $mark;
//        return GeneralUtil::calculateWeightingAverage($marks, true, $this->assign0WhenTheMarkIsNotEntered, false);
    }

    // Calculer la note d'une matiere d'une sequence en considerant les type de notes
    // assign0WhenTheMarkIsNotEntered = true
    function calculateSequenceCourseMeanAverage1 (array $marks): ?float {
        // Considerons les notes null null 5 avec note eliminatoire 7

        // On ne retire rien car on veut calculer la note d'une matiere sur une sequence
        foreach ($marks as $mark) {
            if($mark->getMark() === null){
                $mark->setMark(0);
                $mark->setMarkEntered(0);
            }
        }

        $notNullMarks = array_filter($marks, fn(Mark $mark) => $mark->getMark() !== null);

        // Maintenant si toutes les notes restantes sont nulles => moyenne nulle
        if (empty($notNullMarks)) {
            return 0;
        }

        $totalCredit = 0;
        $total = 0;

        foreach ($marks as $marksObject) {
            $mark = $marksObject->getMark();
            $weighting = $marksObject->getWeighting() ?? 1;
            $total += $mark * $weighting;
            $totalCredit += $weighting;
        }

//        dd($total,$totalCredit);
        $mark = $totalCredit !== 0 ? round(floatval($total / $totalCredit), 2) : null;
        return $mark;
//        return GeneralUtil::calculateWeightingAverage($marks, true, $this->assign0WhenTheMarkIsNotEntered, false);
    }
}