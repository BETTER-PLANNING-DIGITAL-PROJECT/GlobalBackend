<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Course\PrimaryAndSecondary;

// Classe contenant toutes les fonctions concernant le calcul des notes pour une sequence pour les matieres
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\AnnualMarkCalculationUtil;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculated;
use App\Entity\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculated;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Operation\Annual\Course\MarkAnnualCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;

// Il serait preferable de creer un sequence mark calculation pour chaque type (Matiere,Module,Moyenne generale)
class AnnualMarkCalculationCoursePrimaryAndSecondaryUtil extends AnnualMarkCalculationUtil
{
    private bool $isEliminationCourseActivated = false;

    public function __construct(
        // Configurations
        // Calc des notes d'une matiere
        protected string $finalMarkFormula,
        protected bool $assign0WhenTheMarkIsNotEntered,
        protected bool $isMarkForAllSequenceRequired,

        // Attributs principaux
        private array $evaluationPeriodsArray,
        private array $evaluationPeriods,

        // Repository
        private StudentCourseRegistrationRepository       $studentCourseRegistrationRepository,
        private EvaluationPeriodRepository       $evaluationPeriodRepository,
        private readonly MarkSequenceCourseCalculatedRepository $markSequenceCourseCalculatedRepository,
        private readonly MarkPeriodCourseCalculatedRepository $markPeriodCourseCalculatedRepository
    )
    {
        parent::__construct();
        // On ne calcule pas de note annuelle de matiere pour le moment
        $this->calculateMethod = 'calculateAnnualCourseMark'; // Il s'agit toujours d'une moyenne des moyennes donc la configuration n'influence pas la maniere de calculer juste l'ensemble des notes (seq / periode) //.$this->finalMarkFormula;
        $this->getMarksMethod = 'getMarks'.$this->finalMarkFormula;
    }

    // Getters & setters
    public function isEliminationCourseActivated(): bool
    {
        return $this->isEliminationCourseActivated;
    }

    public function setIsEliminationCourseActivated(bool $isEliminationCourseActivated): AnnualMarkCalculationCoursePrimaryAndSecondaryUtil
    {
        $this->isEliminationCourseActivated = $isEliminationCourseActivated;
        return $this;
    }

    public function getCourses(StudentRegistration $student)
    {
        $evaluationPeriod = $this->evaluationPeriods[0];

        return $this->studentCourseRegistrationRepository->findBy(['evaluationPeriod' => $evaluationPeriod, 'StudRegistration' => $student]);
    }

    // Recuperer les notes des sequences pour la periode
    public function getMarks(StudentCourseRegistration $studentCourseRegistration)
    {
        return $this->{$this->getMarksMethod}($studentCourseRegistration);
    }

    // Recuperer les notes des sequences pour toute l'annee
    public function getMarks1(StudentCourseRegistration $studentCourseRegistration)
    {
        $student = $studentCourseRegistration->getStudRegistration();
        $classProgram = $studentCourseRegistration->getClassProgram();
        $codeuvc = $classProgram->getCodeuvc();
        $nameuvc = $classProgram->getNameuvc();

        // Recuperations des autres inscriptions aux matieres pour l'etudiant pour les autres semestres
        $studentCourseRegistrations = [];

        foreach ($this->evaluationPeriods as $evaluationPeriod){
            $studentCourseRegistrations[$evaluationPeriod->getId()] = $this->studentCourseRegistrationRepository->findByStudentOtherEvaluationPeriodRegistration($studentCourseRegistration, $evaluationPeriod);
        }

        $markSequenceCoursesCalculated = [];
        foreach ($this->evaluationPeriodsArray as $evaluationPeriodId=> $sequences) {
            $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);
            foreach ($sequences as $sequence) {
                $markSequenceCoursesCalculated[] = $this->markSequenceCourseCalculatedRepository->findOneBy(['sequence' => $sequence, 'student' => $student, 'evaluationPeriod' => $evaluationPeriod,'studentCourseRegistration'=>$studentCourseRegistrations[$evaluationPeriodId]]);
            }
        }

        return $markSequenceCoursesCalculated;
    }

    // Recuperer les notes des periodes pour la matiere
    public function getMarks2(StudentCourseRegistration $studentCourseRegistration)
    {
        $student = $studentCourseRegistration->getStudRegistration();
        $classProgram = $studentCourseRegistration->getClassProgram();
        $codeuvc = $classProgram->getCodeuvc();
        $nameuvc = $classProgram->getNameuvc();

        // Recuperations des autres inscriptions aux matieres pour l'etudiant pour les autres semestres
        $studentCourseRegistrations = array_map(fn(EvaluationPeriod $evaluationPeriod)=>$this->studentCourseRegistrationRepository->findByStudentOtherEvaluationPeriodRegistration($studentCourseRegistration, $evaluationPeriod),$this->evaluationPeriods);

        return array_map(fn(?StudentCourseRegistration $studentCourseRegistration)=>$this->markPeriodCourseCalculatedRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration,'evaluationPeriod'=>$studentCourseRegistration?->getEvaluationPeriod()]),$studentCourseRegistrations);
    }

    // Savoir s'il existe une note de periode pour la matiere
    public function markExists(StudentCourseRegistration $studentCourseRegistration)
    {
        $markPeriodCourseCalculateds = $this->getMarks2($studentCourseRegistration);
        return !empty(array_filter($markPeriodCourseCalculateds,fn(?MarkPeriodCourseCalculated $markPeriodCourseCalculated)=>isset($markPeriodCourseCalculated)));
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
    }*/

    // Calcul de la note d'une matiere
    // Ici, il n'y a pas de ponderation associee a une sequence / periode pour le calcul de la note annuelle
    // On fait uniquement la moyenne des notes de periode / sequence
    // halfYearAverageFormula = 2
    // assign0WhenTheMarkIsNotEntered = true | false
    // $isMarkForAllSequenceRequired = true | false
    function calculateAnnualCourseMark(array $markCourseCalculateds)
    {
        // Verification
        // Recuperer les notes ($markCourseCalculateds)
        $numberOfMarks = count($markCourseCalculateds);

        // Recuperer les notes generees
        $markCourseCalculatedsGenerated = [];
        foreach ($markCourseCalculateds as $markCourseCalculated) {
            if ($markCourseCalculated) $markCourseCalculatedsGenerated[] = $markCourseCalculated;
        }
        $numberOfMarksGenerated = count($markCourseCalculatedsGenerated);

        // Si aucune note n'est generee sur toutes les sequences
        if ($numberOfMarksGenerated === 0) return null;

        // S'il y a une note non generee
        if ($numberOfMarksGenerated !== $numberOfMarks
            // Si $isMarkForAllRequired = true , renvoyer null
            && $this->isMarkForAllSequenceRequired) return null;
        // Sinon retirer cette note
        // On a deja retire

        // Recuperer les notes saisies
        if ($this->assign0WhenTheMarkIsNotEntered){
            foreach ($markCourseCalculatedsGenerated as $markCourseCalculated) {
                if ($markCourseCalculated->getMark() === null) $markCourseCalculated->setMark(0);
            }
        }
        $marks = array_values(array_filter($markCourseCalculatedsGenerated,fn(MarkSequenceCourseCalculated | MarkPeriodCourseCalculated $markCourseCalculated)=> $markCourseCalculated->getMark() !== null));
        $numberOfMarksEntered = count($marks);

        // S'il y a une note non saisie
        if ($numberOfMarksGenerated !== $numberOfMarksEntered
            // Si $assign0WhenTheMarkIsNotEntered = false , renvoyer null
            && $this->isMarkForAllSequenceRequired && !$this->assign0WhenTheMarkIsNotEntered) return null;
        // Sinon continuer, on manage ces cas plus tard

        // Calculer la note suivant la formule
        $sum = 0;
        $marks = array_map(fn(MarkSequenceCourseCalculated | MarkPeriodCourseCalculated $markCourseCalculated)=>$markCourseCalculated->getMark(),$marks);
        foreach ($marks as $mark) {
            $sum += floatval($mark);
        }
        $mark = $numberOfMarksEntered !== 0 ? round(floatval($sum / $numberOfMarksEntered),2) : ($this->assign0WhenTheMarkIsNotEntered ? 0 : null);
        return $mark;
    }

}