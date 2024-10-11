<?php

namespace App\Controller\School\Exam\EndProcessing\MarkValidation\Services\Sequence;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculationRelationRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use Doctrine\ORM\EntityManagerInterface;

class SequenceMarkValidationValidateDeleteUtil
{

    private \Closure $dontClassifyTheExcludedFn;

    public function __construct(
        protected bool                                   $calculateSubjectsRanks,
        protected bool                                   $dontClassifyTheExcluded,
        private string $validateMethod,
        private readonly EvaluationPeriod $evaluationPeriod, 
        private readonly Sequence $sequence,

        private readonly ClassProgramRepository $classProgramRepository,
        private readonly MarkSequenceCourseCalculatedRepository                  $markSequenceCourseCalculatedRepository,
        private readonly MarkSequenceCourseCalculationRelationRepository         $markSequenceCourseCalculationRelationRepository,

        private readonly MarkSequenceModuleCalculatedRepository                  $markSequenceModuleCalculatedRepository,
        private readonly MarkSequenceModuleCalculationRelationRepository         $markSequenceModuleCalculationRelationRepository,

        private readonly MarkSequenceGeneralAverageCalculatedRepository          $markSequenceGeneralAverageCalculatedRepository,
        private readonly MarkSequenceGeneralAverageCalculationRelationRepository $markSequenceGeneralAverageCalculationRelationRepository,

        private readonly EntityManagerInterface $entityManager
    )
    {
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

    function validateStudentMarks(StudentRegistration $student,bool $validate = true):string
    {
        $criteria = ['student' => $student, 'evaluationPeriod' => $this->evaluationPeriod, 'sequence' => $this->sequence];
        $markSequenceCourseCalculationRelations = $this->markSequenceCourseCalculationRelationRepository->findBy($criteria);
        $markSequenceModuleCalculationRelations = $this->markSequenceModuleCalculationRelationRepository->findBy($criteria);
        $markSequenceGeneralAverageCalculationRelations = $this->markSequenceGeneralAverageCalculationRelationRepository->findBy($criteria);

        $markSequenceCourseCalculateds = $this->markSequenceCourseCalculatedRepository->findBy($criteria);
        $markSequenceModuleCalculateds = $this->markSequenceModuleCalculatedRepository->findBy($criteria);
        $markSequenceGeneralAverageCalculateds = $this->markSequenceGeneralAverageCalculatedRepository->findBy($criteria);


            foreach ($markSequenceCourseCalculationRelations as $markSequenceCalculationRelation) {
                $markSequenceCalculationRelation->setIsValidated($validate);
            }
            foreach ($markSequenceModuleCalculationRelations as $markSequenceCalculationRelation) {
                $markSequenceCalculationRelation->setIsValidated($validate);
            }
            foreach ($markSequenceGeneralAverageCalculationRelations as $markSequenceCalculationRelation) {
                $markSequenceCalculationRelation->setIsValidated($validate);
            }

            foreach ($markSequenceCourseCalculateds as $markSequenceCalculated) {
                $markSequenceCalculated->setIsValidated($validate);
            }
            foreach ($markSequenceModuleCalculateds as $markSequenceCalculated) {
                $markSequenceCalculated->setIsValidated($validate);
            }
            foreach ($markSequenceGeneralAverageCalculateds as $markSequenceCalculated) {
                $markSequenceCalculated->setIsValidated($validate);
            }
        $this->entityManager->flush();

        return 'success';
    }

    function unvalidateStudentMarks(StudentRegistration $student){
        return $this->validateStudentMarks($student, false);
    }

    function deleteStudentMarks(StudentRegistration $student)
    {
        $criteria = ['student' => $student, 'evaluationPeriod' => $this->evaluationPeriod, 'sequence' => $this->sequence];
        $markSequenceCourseCalculationRelations = $this->markSequenceCourseCalculationRelationRepository->findBy($criteria);
        $markSequenceModuleCalculationRelations = $this->markSequenceModuleCalculationRelationRepository->findBy($criteria);
        $markSequenceGeneralAverageCalculationRelations = $this->markSequenceGeneralAverageCalculationRelationRepository->findBy($criteria);

        $markSequenceCourseCalculateds = $this->markSequenceCourseCalculatedRepository->findBy($criteria);
        $markSequenceModuleCalculateds = $this->markSequenceModuleCalculatedRepository->findBy($criteria);
        $markSequenceGeneralAverageCalculateds = $this->markSequenceGeneralAverageCalculatedRepository->findBy($criteria);

            foreach ($markSequenceCourseCalculationRelations as $markSequenceCalculationRelation) {
                if (!$markSequenceCalculationRelation->getIsValidated()) $this->entityManager->remove($markSequenceCalculationRelation);
            }
            foreach ($markSequenceModuleCalculationRelations as $markSequenceCalculationRelation) {
                if (!$markSequenceCalculationRelation->getIsValidated()) $this->entityManager->remove($markSequenceCalculationRelation);
            }
            foreach ($markSequenceGeneralAverageCalculationRelations as $markSequenceCalculationRelation) {
                if (!$markSequenceCalculationRelation->getIsValidated()) $this->entityManager->remove($markSequenceCalculationRelation);
            }

            foreach ($markSequenceCourseCalculateds as $markSequenceCalculated) {
                if (!$markSequenceCalculated->getIsValidated()) $this->entityManager->remove($markSequenceCalculated);
            }
            foreach ($markSequenceModuleCalculateds as $markSequenceCalculated) {
                if (!$markSequenceCalculated->getIsValidated()) $this->entityManager->remove($markSequenceCalculated);
            }
            foreach ($markSequenceGeneralAverageCalculateds as $markSequenceCalculated) {
                if (!$markSequenceCalculated->getIsValidated()) $this->entityManager->remove($markSequenceCalculated);
            }
            $this->entityManager->flush();

        return 'success';
    }

    function validateOrDeleteStudentsMarks(array $students){
        $validateMethod = $this->validateMethod . 'StudentMarks';
        foreach ($students as $student) {
            $this->{$validateMethod}($student);
        }

        if ($this->validateMethod === 'delete') {
            $class = $students[0]->getCurrentClass();

            // Mettre les rangs sur les matieres
            if ($this->calculateSubjectsRanks) {
                $classProgramIds = $this->markSequenceCourseCalculatedRepository->getClassPrograms($class, $this->evaluationPeriod, $this->sequence);
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
                }
                else {
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
            $markSequenceGeneralAverageCalculateds = $this->markSequenceGeneralAverageCalculatedRepository->findBy(['class' => $class, 'evaluationPeriod' => $this->evaluationPeriod, 'sequence' => $this->sequence, 'isClassed' => true], ['average' => 'DESC']);
            if ($this->dontClassifyTheExcluded) $markSequenceGeneralAverageCalculateds = ($this->dontClassifyTheExcludedFn)($markSequenceGeneralAverageCalculateds);
            $totalStudentsClassed = count($markSequenceGeneralAverageCalculateds);
            foreach ($markSequenceGeneralAverageCalculateds as $index => $markSequenceGeneralAverageRankingCalculated) {
                $markSequenceGeneralAverageRankingCalculated->setRank($index + 1);
                $markSequenceGeneralAverageRankingCalculated->setTotalStudentsClassed($totalStudentsClassed);
            }

            $this->entityManager->flush();
        }

        return 'success';
    }

    // Fonction principale
    function validateOrDeleteMarks(string $case,mixed $element){
        return $this->{'validateOrDelete'.$case.'Marks'}($element);
    }

}