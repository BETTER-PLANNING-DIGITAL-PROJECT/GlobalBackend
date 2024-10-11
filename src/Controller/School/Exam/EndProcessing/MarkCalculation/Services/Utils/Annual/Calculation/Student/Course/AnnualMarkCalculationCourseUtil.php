<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Course;

// Classe contenant toutes les fonctions concernant le calcul des notes pour une sequence pour les matieres
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\AnnualMarkCalculationUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Calculation\Student\PeriodMarkCalculationUtil;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculated;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Proxies\__CG__\App\Entity\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculated;

// Il serait preferable de creer un sequence mark calculation pour chaque type (Matiere,Module,Moyenne generale)
class AnnualMarkCalculationCourseUtil extends AnnualMarkCalculationUtil
{
    private bool $isEliminationCourseActivated = false;

    public function __construct(
        // Configurations
        // Calc des notes d'une matiere
//        protected string $halfYearAverageFormula,
//        protected bool $isMarkForAllSequenceRequired,

        // Attributs principaux
        private array $evaluationPeriods,

        // Repository
        private StudentCourseRegistrationRepository       $studentCourseRegistrationRepository,
        private readonly MarkPeriodCourseCalculatedRepository $markPeriodCourseCalculatedRepository
    )
    {
        parent::__construct();
        // On ne calcule pas de note annuelle de matiere pour le moment
//        $this->calculateMethod = 'calculatePeriodCourseMark'.$this->halfYearAverageFormula;
    }

    // Getters & setters
    public function isEliminationCourseActivated(): bool
    {
        return $this->isEliminationCourseActivated;
    }

    public function setIsEliminationCourseActivated(bool $isEliminationCourseActivated): AnnualMarkCalculationCourseUtil
    {
        $this->isEliminationCourseActivated = $isEliminationCourseActivated;
        return $this;
    }

    public function getCourses(StudentRegistration $student)
    {
        /*$studentCourseRegistrations = [];
        foreach ($this->evaluationPeriods as $evaluationPeriod) {
            foreach ($this->studentCourseRegistrationRepository->findBy(['evaluationPeriod' => $evaluationPeriod, 'StudRegistration' => $student]) as $studentCourseRegistration) {
                $studentCourseRegistration[] = $studentCourseRegistration;
            }
        }*/
        return array_merge(...array_map(fn(EvaluationPeriod $evaluationPeriod)=>$this->studentCourseRegistrationRepository->findBy(['evaluationPeriod' => $evaluationPeriod, 'StudRegistration' => $student]),$this->evaluationPeriods));
    }

    // Recuperer les notes des sequences pour la periode
    public function getMarks(StudentCourseRegistration $studentCourseRegistration)
    {
        return $this->markPeriodCourseCalculatedRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration,'evaluationPeriod'=>$studentCourseRegistration->getEvaluationPeriod()]);
    }

    // Savoir s'il existe une note de sequence pour la matiere
    public function markExists(StudentCourseRegistration $studentCourseRegistration)
    {
        $marks = $this->getMarks($studentCourseRegistration);
        return isset($marks);
    }

    // Calcul de la note de periode d'une matiere
    // halfYearAverageFormula = 1
    // assign0WhenTheMarkIsNotEntered = true | false
    // $isMarkForAllSequenceRequired = true | false
    /*function calculatePeriodCourseMark1(array $markSequenceCourseCalculateds)
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

        // Si aucune note n'est generee sur toutes les sequences
        if ($numberOfMarksGenerated === 0) return null;

        // S'il y a une note non generee
        if ($numberOfMarksGenerated !== $numberOfMarks
            // Si $isMarkForAllSequenceRequired = true , renvoyer null
            && $this->isMarkForAllSequenceRequired) return null;
        // Sinon retirer cette note
        // On a deja retire

        // Recuperer les notes saisies
        if ($this->assign0WhenTheMarkIsNotEntered){
            foreach ($markSequenceCourseCalculatedsGenerated as $markSequenceCourseCalculated) {
                if ($markSequenceCourseCalculated->getMark() === null) $markSequenceCourseCalculated->setMark(0);
            }
        }
        $markSequenceCourseCalculateds = array_values(array_filter($markSequenceCourseCalculatedsGenerated,fn(MarkSequenceCourseCalculated $markSequenceCourseCalculated)=> $markSequenceCourseCalculated->getMark() !== null));
        $numberOfMarksEntered = count($markSequenceCourseCalculateds);

        // S'il y a une note non saisie
        if ($numberOfMarksGenerated !== $numberOfMarksEntered
            // Si $assign0WhenTheMarkIsNotEntered = false , renvoyer null
            && $this->isMarkForAllSequenceRequired && !$this->assign0WhenTheMarkIsNotEntered) return null;
        // Sinon continuer, on manage ces cas plus tard

        // Calculer la note suivant la formule
        $sum = $totalCredit = 0;
        foreach ($markSequenceCourseCalculateds as $i=>$markSequenceCourseCalculated) {
            $mark = $markSequenceCourseCalculated->getMark();
            $credit = $this->weightings[$markSequenceCourseCalculated->getSequence()->getId()];
            $sum += floatval($mark) * $credit;
            $totalCredit += $credit;
        }
        $mark = $totalCredit !== 0 ? round(floatval($sum / $totalCredit),2) : ($this->assign0WhenTheMarkIsNotEntered ? 0 : null);
        return $mark;
    }

    // halfYearAverageFormula = 2
    // assign0WhenTheMarkIsNotEntered = true | false
    // $isMarkForAllSequenceRequired = true | false
    function calculatePeriodCourseMark2(array $markSequenceCourseCalculateds)
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

        // Si aucune note n'est generee sur toutes les sequences
        if ($numberOfMarksGenerated === 0) return null;

        // S'il y a une note non generee
        if ($numberOfMarksGenerated !== $numberOfMarks
            // Si $isMarkForAllSequenceRequired = true , renvoyer null
            && $this->isMarkForAllSequenceRequired) return null;
        // Sinon retirer cette note
        // On a deja retire

        // Recuperer les notes saisies
        if ($this->assign0WhenTheMarkIsNotEntered){
            foreach ($markSequenceCourseCalculatedsGenerated as $markSequenceCourseCalculated) {
                if ($markSequenceCourseCalculated->getMark() === null) $markSequenceCourseCalculated->setMark(0);
            }
        }
        $marks = array_values(array_filter($markSequenceCourseCalculatedsGenerated,fn(MarkSequenceCourseCalculated $markSequenceCourseCalculated)=> $markSequenceCourseCalculated->getMark() !== null));
        $numberOfMarksEntered = count($marks);

        // S'il y a une note non saisie
        if ($numberOfMarksGenerated !== $numberOfMarksEntered
            // Si $assign0WhenTheMarkIsNotEntered = false , renvoyer null
            && $this->isMarkForAllSequenceRequired && !$this->assign0WhenTheMarkIsNotEntered) return null;
        // Sinon continuer, on manage ces cas plus tard

        // Calculer la note suivant la formule
        $sum = 0;
        $marks = array_map(fn(MarkSequenceCourseCalculated $markSequenceCourseCalculated)=>$markSequenceCourseCalculated->getMark(),$marks);
        foreach ($marks as $mark) {
            $sum += floatval($mark);
        }
        $mark = $numberOfMarksEntered !== 0 ? round(floatval($sum / $numberOfMarksEntered),2) : ($this->assign0WhenTheMarkIsNotEntered ? 0 : null);
        return $mark;
    }*/
}