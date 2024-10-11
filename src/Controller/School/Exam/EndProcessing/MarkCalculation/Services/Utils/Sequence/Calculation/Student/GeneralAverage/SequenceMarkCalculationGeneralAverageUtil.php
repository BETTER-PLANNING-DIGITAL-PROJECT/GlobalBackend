<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\GeneralAverage;

// Classe contenant toutes les fonctions concernant le calcul des notes pour une sequence
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\ClassificationUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\ClassificationSequenceUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\Module\SequenceMarkCalculationModuleUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\WorkRemarksSequenceUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Generation\Student\GeneralAverage\SequenceMarkGenerationGeneralAverageUtil;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculated;
use App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculated;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculatedRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Closure;

// Il serait preferable de creer un sequence mark calculation pour chaque type (Matiere,Module,Moyenne generale)
class SequenceMarkCalculationGeneralAverageUtil extends SequenceMarkCalculationModuleUtil
{
    private bool $isEliminationGeneralAverageActivated;
    private ?float $eliminateAverage;
    private string $calculateGeneralAverageMethod;

    private \Closure $dontClassifyTheExcludedFn;

    public function __construct(
        // Configurations
        // Calc des notes d'une matiere
        protected bool                                         $assign0WhenTheMarkIsNotEntered,
        protected bool                                         $isCoefficientOfNullMarkConsiderInTheAverageCalculation,
        protected bool                                         $calculateSubjectsRanks,

        // Calc de la moyenne generale d'une matiere
        private bool                                           $activateEliminationMarks,

        // Autres ou commun au deux
        protected bool                                         $dontClassifyTheExcluded,

        // Attributs principaux
        private readonly SchoolClass                           $class,
        private readonly EvaluationPeriod                      $evaluationPeriod,
        private readonly Sequence                              $sequence,

        // Utils
        private SequenceMarkGenerationGeneralAverageUtil       $sequenceMarkGenerationStudentGeneralAverageUtil,
        private ClassificationSequenceUtil                     $classificationUtil,
        private WorkRemarksSequenceUtil                        $workRemarksSequenceUtil,

        // Repository
        private ClassProgramRepository                         $classProgramRepository,
        private MarkSequenceGeneralAverageCalculatedRepository $markSequenceGeneralAverageCalculatedRepository,
        private MarkSequenceCourseCalculatedRepository         $markSequenceCourseCalculatedRepository,

        // Manager
        private readonly EntityManagerInterface                $entityManager
    )
    {
        parent::__construct($this->assign0WhenTheMarkIsNotEntered, $this->isCoefficientOfNullMarkConsiderInTheAverageCalculation);
        $this->eliminateAverage = $this->sequence->getEliminateAverage();
        $this->isEliminationGeneralAverageActivated = $this->activateEliminationMarks && isset($this->eliminateAverage);//$this->activateEliminationMarks && $eliminateAverage;
//        dd($this->isEliminationGeneralAverageActivated);
        // La methode de calcul des notes est la meme que celle du module car il s'agit d'un calcul pondere de notes juste que l'ensemble de notes differe

//        dd($this->isEliminationGeneralAverageActivated());
        $this->calculateGeneralAverageMethod = 'calculateSequenceGeneralAverageEvery' . (int)($this->classificationUtil->isEveryBodyClassed()) . 'ElimGen' . (int)($this->isEliminationGeneralAverageActivated);
//        dd($this->calculateGeneralAverageMethod);

        $this->dontClassifyTheExcludedFn =
                fn(array $markCalculateds) => array_values(array_filter($markCalculateds,
                    function (mixed $markCalculated) {
                        $status = $markCalculated->getStudent()->getStatus();
                        $isResigned = $status === 'resigned';
                        if ($isResigned) {
                            $markCalculated->setIsClassed(false);
                            $markCalculated->setRank(null);
                        }
                        return !$isResigned;
                    }
                ));
    }

    public function isEliminationGeneralAverageActivated(): bool
    {
        return $this->isEliminationGeneralAverageActivated;
    }

    public function setIsEliminationGeneralAverageActivated(bool $isEliminationGeneralAverageActivated): SequenceMarkCalculationGeneralAverageUtil
    {
        $this->isEliminationGeneralAverageActivated = $isEliminationGeneralAverageActivated;
        return $this;
    }

    public function getEliminateAverage(): float
    {
        return $this->eliminateAverage;
    }

    public function setEliminateAverage(float $eliminateAverage): SequenceMarkCalculationGeneralAverageUtil
    {
        $this->eliminateAverage = $eliminateAverage;
        return $this;
    }

    public function getSequenceMarkGenerationStudentGeneralAverageUtil(): SequenceMarkGenerationGeneralAverageUtil
    {
        return $this->sequenceMarkGenerationStudentGeneralAverageUtil;
    }

    public function setSequenceMarkGenerationStudentGeneralAverageUtil(SequenceMarkGenerationGeneralAverageUtil $sequenceMarkGenerationStudentGeneralAverageUtil): SequenceMarkCalculationGeneralAverageUtil
    {
        $this->sequenceMarkGenerationStudentGeneralAverageUtil = $sequenceMarkGenerationStudentGeneralAverageUtil;
        return $this;
    }

    // Fonctions de calculs
    // Calculer la moyenne generale d'une sequence
    function calculateSequenceGeneralAverage(array $markSequenceCourseCalculateds, array $studentCourseRegistrationsAttended): MarkSequenceGeneralAverageCalculated
    {
        $markSequenceGeneralAverageCalculated = $this->{$this->calculateGeneralAverageMethod}($markSequenceCourseCalculateds, $studentCourseRegistrationsAttended);
        $this->entityManager->flush();

        // Mettre les remarques sur les moyennes
        $this->workRemarksSequenceUtil->setRemarks($markSequenceGeneralAverageCalculated);

        // Mettre les rangs sur les matieres
        if ($this->calculateSubjectsRanks) {
            $classProgramIds = $this->markSequenceCourseCalculatedRepository->getClassPrograms($this->class, $this->evaluationPeriod, $this->sequence);
            $classPrograms = array_map(fn(int $classProgramId) => $this->classProgramRepository->find($classProgramId), array_column($classProgramIds, 'classProgramId'));
            if ($this->dontClassifyTheExcluded) {
                foreach ($classPrograms as $classProgram) {
                    $markSequenceCourseRankingCalculateds = $this->markSequenceCourseCalculatedRepository->findBy(['classProgram' => $classProgram, 'sequence' => $this->sequence], ['mark' => 'DESC']);
                    $markSequenceCourseRankingCalculateds = ($this->dontClassifyTheExcludedFn)($markSequenceCourseRankingCalculateds);
                    $totalCourseStudentsRegistered = count($markSequenceCourseRankingCalculateds);
                    foreach ($markSequenceCourseRankingCalculateds as $index => $markSequenceCourseRankingCalculated) {
                        $markSequenceCourseRankingCalculated->setRank($index + 1);
                        $markSequenceCourseRankingCalculated->setTotalCourseStudentsRegistered($totalCourseStudentsRegistered);
                    }
                }
            } else {
                foreach ($classPrograms as $classProgram) {
                    $markSequenceCourseRankingCalculateds = $this->markSequenceCourseCalculatedRepository->findBy(['classProgram' => $classProgram, 'sequence' => $this->sequence], ['mark' => 'DESC']);
                    $totalCourseStudentsRegistered = count($markSequenceCourseRankingCalculateds);
                    foreach ($markSequenceCourseRankingCalculateds as $index => $markSequenceCourseRankingCalculated) {
                        $markSequenceCourseRankingCalculated->setRank($index + 1);
                        $markSequenceCourseRankingCalculated->setTotalCourseStudentsRegistered($totalCourseStudentsRegistered);
                    }
                }
            }
        }

        // Mettre les rangs sur les moyennes generales
        $markSequenceGeneralAverageCalculateds = $this->markSequenceGeneralAverageCalculatedRepository->findBy(['class' => $this->class, 'evaluationPeriod' => $this->evaluationPeriod, 'sequence' => $this->sequence, 'isClassed' => true], ['average' => 'DESC']);
        if ($this->dontClassifyTheExcluded) $markSequenceGeneralAverageCalculateds = ($this->dontClassifyTheExcludedFn)($markSequenceGeneralAverageCalculateds);
        $totalStudentsClassed = count($markSequenceGeneralAverageCalculateds);
        foreach ($markSequenceGeneralAverageCalculateds as $index => $markSequenceGeneralAverageRankingCalculated) {
            $markSequenceGeneralAverageRankingCalculated->setRank($index + 1);
            $markSequenceGeneralAverageRankingCalculated->setTotalStudentsClassed($totalStudentsClassed);
        }

        $this->entityManager->flush();
        return $markSequenceGeneralAverageCalculated;
    }

    // Calculer la moyenne generale d'une sequence
    // $isEveryBodyClassed = false
    // $isEliminationGeneralAverageActivated = false|true
    function calculateSequenceGeneralAverageEvery0(array $markSequenceCourseCalculateds, array $studentCourseRegistrationsAttended): MarkSequenceGeneralAverageCalculated
    {
        $markSequenceGeneralAverageCalculated = $this->calculateSequenceGeneralAverageEvery1($markSequenceCourseCalculateds, $studentCourseRegistrationsAttended);
        $isClassed = $markSequenceGeneralAverageCalculated->getIsClassed();
        if (!$isClassed) {
            $markSequenceGeneralAverageCalculated->setAverage(null);
            $markSequenceGeneralAverageCalculated->setAverageGpa(null);
            $markSequenceGeneralAverageCalculated->setIsClassed(false);
        }
        return $markSequenceGeneralAverageCalculated;
    }

    // Calculer la moyenne generale d'une sequence
    // $isEveryBodyClassed = false
    // $isEliminationGeneralAverageActivated = false
    // 0 0
    function calculateSequenceGeneralAverageEvery0ElimGen0(array $markSequenceCourseCalculateds, array $studentCourseRegistrationsAttended): MarkSequenceGeneralAverageCalculated
    {
        $markSequenceGeneralAverageCalculated = $this->calculateSequenceGeneralAverageEvery0($markSequenceCourseCalculateds, $studentCourseRegistrationsAttended);
        return $markSequenceGeneralAverageCalculated;
    }

    // $isEveryBodyClassed = false
    // $isEliminationGeneralAverageActivated = true
    // 0 1
    function calculateSequenceGeneralAverageEvery0ElimGen1(array $markSequenceCourseCalculateds, array $studentCourseRegistrationsAttended): MarkSequenceGeneralAverageCalculated
    {
        $markSequenceGeneralAverageCalculated = $this->calculateSequenceGeneralAverageEvery0($markSequenceCourseCalculateds, $studentCourseRegistrationsAttended);
        $average = $markSequenceGeneralAverageCalculated->getAverage();
        $markSequenceGeneralAverageCalculated->setIsEliminated(isset($average) && $average < $this->eliminateAverage);
        return $markSequenceGeneralAverageCalculated;
    }

    // Calculer la moyenne generale d'une sequence
    // $isEveryBodyClassed = true
    // $isEliminationGeneralAverageActivated = false|true
    function calculateSequenceGeneralAverageEvery1(array $markSequenceCourseCalculateds, array $studentCourseRegistrationsAttended): MarkSequenceGeneralAverageCalculated
    {
        // Calcul de la moyenne generale et enregistrements
        $classificationDatas = $this->classificationUtil->isClassedWithCourses($studentCourseRegistrationsAttended, $markSequenceCourseCalculateds);
        extract($classificationDatas);
        $markDatas = $this->calculateMark($markSequenceCourseCalculateds);
        $average = null;
        $averageGpa = null;
        if (is_array($markDatas)) {
            $average = $markDatas['mark'];
            $averageGpa = $markDatas['averageGpa'];
        } else if (is_float($markDatas)) {
            $average = $markDatas;
        }
        $markSequenceGeneralAverageCalculated = $this->sequenceMarkGenerationStudentGeneralAverageUtil->generateSequenceGeneralAverageCalculated($average, $averageGpa, $numberOfAttendedCourses, $numberOfComposedCourses, $totalOfCreditsAttended, $totalOfCreditsComposed,
            $percentageSubjectNumber,
            $percentageTotalCoefficient,
            $isClassed,
            $markSequenceCourseCalculateds);

        return $markSequenceGeneralAverageCalculated;
    }

    // $isEveryBodyClassed = true
    // $isEliminationGeneralAverageActivated = false
    // 1 0
    function calculateSequenceGeneralAverageEvery1ElimGen0(array $markSequenceCourseCalculateds, array $studentCourseRegistrationsAttended): MarkSequenceGeneralAverageCalculated
    {
        $markSequenceGeneralAverageCalculated = $this->calculateSequenceGeneralAverageEvery1($markSequenceCourseCalculateds, $studentCourseRegistrationsAttended);
        return $markSequenceGeneralAverageCalculated;
    }

    // $isEveryBodyClassed = true
    // $isEliminationGeneralAverageActivated = true
    // 1 1
    function calculateSequenceGeneralAverageEvery1ElimGen1(array $markSequenceCourseCalculateds, array $studentCourseRegistrationsAttended): MarkSequenceGeneralAverageCalculated
    {
        $markSequenceGeneralAverageCalculated = $this->calculateSequenceGeneralAverageEvery1($markSequenceCourseCalculateds, $studentCourseRegistrationsAttended);
        $average = $markSequenceGeneralAverageCalculated->getAverage();
        $markSequenceGeneralAverageCalculated->setIsEliminated(isset($average) && $average < $this->eliminateAverage);
        return $markSequenceGeneralAverageCalculated;
    }
}