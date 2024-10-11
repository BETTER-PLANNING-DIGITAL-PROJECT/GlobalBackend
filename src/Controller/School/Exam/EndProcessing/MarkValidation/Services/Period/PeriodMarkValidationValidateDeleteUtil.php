<?php

namespace App\Controller\School\Exam\EndProcessing\MarkValidation\Services\Period;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCourseCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageSequenceCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculatedRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use Doctrine\ORM\EntityManagerInterface;

class PeriodMarkValidationValidateDeleteUtil
{

    private \Closure $dontClassifyTheExcludedFn;

    public function __construct(
        protected bool                                                                 $calculateSubjectsRanks,
        protected bool                                                                 $dontClassifyTheExcluded,
        private string                                                                 $validateMethod,
        private readonly EvaluationPeriod                                              $evaluationPeriod,
        private readonly ClassProgramRepository                                        $classProgramRepository,
        private readonly MarkPeriodCourseCalculatedRepository                          $markPeriodCourseCalculatedRepository,
        private readonly MarkPeriodCourseCalculationRelationRepository         $markPeriodCourseCalculationRelationRepository,

        private readonly MarkPeriodModuleCalculatedRepository                          $markPeriodModuleCalculatedRepository,
        private readonly MarkPeriodModuleCalculationRelationRepository                 $markPeriodModuleCalculationRelationRepository,

        private readonly MarkPeriodGeneralAverageCalculatedRepository                  $markPeriodGeneralAverageCalculatedRepository,
        private readonly MarkPeriodGeneralAverageSequenceCalculationRelationRepository $markPeriodGeneralAverageSequenceCalculationRelationRepository,
        private readonly MarkPeriodGeneralAverageCourseCalculationRelationRepository   $markPeriodGeneralAverageCourseCalculationRelationRepository,

        private readonly EntityManagerInterface                                        $entityManager
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

    function validateStudentMarks(StudentRegistration $student, bool $validate = true): string
    {
        $criteria = ['student' => $student, 'evaluationPeriod' => $this->evaluationPeriod];
        $markPeriodCourseCalculationRelations = $this->markPeriodCourseCalculationRelationRepository->findBy($criteria);
        $markPeriodModuleCalculationRelations = $this->markPeriodModuleCalculationRelationRepository->findBy($criteria);
        $markPeriodGeneralAverageSequenceCalculationRelations = $this->markPeriodGeneralAverageSequenceCalculationRelationRepository->findBy($criteria);
        $markPeriodGeneralAverageCourseCalculationRelations = $this->markPeriodGeneralAverageCourseCalculationRelationRepository->findBy($criteria);

        $markPeriodCourseCalculateds = $this->markPeriodCourseCalculatedRepository->findBy($criteria);
        $markPeriodModuleCalculateds = $this->markPeriodModuleCalculatedRepository->findBy($criteria);
        $markPeriodGeneralAverageCalculateds = $this->markPeriodGeneralAverageCalculatedRepository->findBy($criteria);


        foreach ($markPeriodCourseCalculationRelations as $markPeriodCalculationRelation) {
            $markPeriodCalculationRelation->setIsValidated($validate);
        }
        foreach ($markPeriodModuleCalculationRelations as $markPeriodCalculationRelation) {
            $markPeriodCalculationRelation->setIsValidated($validate);
        }
        foreach ($markPeriodGeneralAverageSequenceCalculationRelations as $markPeriodCalculationRelation) {
            $markPeriodCalculationRelation->setIsValidated($validate);
        }

        foreach ($markPeriodGeneralAverageCourseCalculationRelations as $markPeriodCalculationRelation) {
            $markPeriodCalculationRelation->setIsValidated($validate);
        }

        foreach ($markPeriodCourseCalculateds as $markPeriodCalculated) {
            $markPeriodCalculated->setIsValidated($validate);
        }
        foreach ($markPeriodModuleCalculateds as $markPeriodCalculated) {
            $markPeriodCalculated->setIsValidated($validate);
        }
        foreach ($markPeriodGeneralAverageCalculateds as $markPeriodCalculated) {
            $markPeriodCalculated->setIsValidated($validate);
        }
        $this->entityManager->flush();

        return 'success';
    }

    function unvalidateStudentMarks(StudentRegistration $student)
    {
        return $this->validateStudentMarks($student, false);
    }

    function deleteStudentMarks(StudentRegistration $student)
    {
        $criteria = ['student' => $student, 'evaluationPeriod' => $this->evaluationPeriod];
        $markPeriodCourseCalculationRelations = $this->markPeriodCourseCalculationRelationRepository->findBy($criteria);
        $markPeriodModuleCalculationRelations = $this->markPeriodModuleCalculationRelationRepository->findBy($criteria);
        $markPeriodGeneralAverageSequenceCalculationRelations = $this->markPeriodGeneralAverageSequenceCalculationRelationRepository->findBy($criteria);
        $markPeriodGeneralAverageCourseCalculationRelations = $this->markPeriodGeneralAverageCourseCalculationRelationRepository->findBy($criteria);

        $markPeriodCourseCalculateds = $this->markPeriodCourseCalculatedRepository->findBy($criteria);
        $markPeriodModuleCalculateds = $this->markPeriodModuleCalculatedRepository->findBy($criteria);
        $markPeriodGeneralAverageCalculateds = $this->markPeriodGeneralAverageCalculatedRepository->findBy($criteria);

        foreach ($markPeriodCourseCalculationRelations as $markPeriodCalculationRelation) {
            if (!$markPeriodCalculationRelation->getIsValidated()) $this->entityManager->remove($markPeriodCalculationRelation);
        }
        foreach ($markPeriodModuleCalculationRelations as $markPeriodCalculationRelation) {
            if (!$markPeriodCalculationRelation->getIsValidated()) $this->entityManager->remove($markPeriodCalculationRelation);
        }
        foreach ($markPeriodGeneralAverageSequenceCalculationRelations as $markPeriodCalculationRelation) {
            if (!$markPeriodCalculationRelation->getIsValidated()) $this->entityManager->remove($markPeriodCalculationRelation);
        }
        foreach ($markPeriodGeneralAverageCourseCalculationRelations as $markPeriodCalculationRelation) {
            if (!$markPeriodCalculationRelation->getIsValidated()) $this->entityManager->remove($markPeriodCalculationRelation);
        }

        foreach ($markPeriodCourseCalculateds as $markPeriodCalculated) {
            if (!$markPeriodCalculated->getIsValidated()) $this->entityManager->remove($markPeriodCalculated);
        }
        foreach ($markPeriodModuleCalculateds as $markPeriodCalculated) {
            if (!$markPeriodCalculated->getIsValidated()) $this->entityManager->remove($markPeriodCalculated);
        }
        foreach ($markPeriodGeneralAverageCalculateds as $markPeriodCalculated) {
            if (!$markPeriodCalculated->getIsValidated()) $this->entityManager->remove($markPeriodCalculated);
        }
        $this->entityManager->flush();
        return 'success';
    }

    function validateOrDeleteStudentsMarks(array $students)
    {
        $validateMethod = $this->validateMethod . 'StudentMarks';
        foreach ($students as $student) {
            $this->{$validateMethod}($student);
        }

        if ($this->validateMethod === 'delete') {
            $class = $students[0]->getCurrentClass();

            // Mettre les rangs sur les matieres
            if ($this->calculateSubjectsRanks) {
                $classProgramIds = $this->markPeriodCourseCalculatedRepository->getClassPrograms($class, $this->evaluationPeriod);
                $classPrograms = array_map(fn(int $classProgramId) => $this->classProgramRepository->find($classProgramId), array_column($classProgramIds, 'classProgramId'));
                if ($this->dontClassifyTheExcluded) {
                    foreach ($classPrograms as $classProgram) {
                        $markPeriodCourseRankingCalculateds = $this->markPeriodCourseCalculatedRepository->findBy(['classProgram' => $classProgram], ['mark' => 'DESC']);
                        $markPeriodCourseRankingCalculateds = ($this->dontClassifyTheExcludedFn)($markPeriodCourseRankingCalculateds);
                        $totalCourseStudentsRegistered = count($markPeriodCourseRankingCalculateds);
                        foreach ($markPeriodCourseRankingCalculateds as $index => $markPeriodCourseRankingCalculated) {
                            $markPeriodCourseRankingCalculated->setRank($index + 1);
                            $markPeriodCourseRankingCalculated->setTotalCourseStudentsRegistered($totalCourseStudentsRegistered);
                        }
                    }
                }
                else {
                    foreach ($classPrograms as $classProgram) {
                        $markPeriodCourseRankingCalculateds = $this->markPeriodCourseCalculatedRepository->findBy(['classProgram' => $classProgram], ['mark' => 'DESC']);
                        $totalCourseStudentsRegistered = count($markPeriodCourseRankingCalculateds);
                        foreach ($markPeriodCourseRankingCalculateds as $index => $markPeriodCourseRankingCalculated) {
                            $markPeriodCourseRankingCalculated->setRank($index + 1);
                            $markPeriodCourseRankingCalculated->setTotalCourseStudentsRegistered($totalCourseStudentsRegistered);
                        }
                    }
                }
            }

            // Mettre les rangs sur les moyennes generales
            $markPeriodGeneralAverageCalculateds = $this->markPeriodGeneralAverageCalculatedRepository->findBy(['class' => $class, 'evaluationPeriod' => $this->evaluationPeriod, 'isClassed' => true], ['average' => 'DESC']);
            if ($this->dontClassifyTheExcluded) $markPeriodGeneralAverageCalculateds = ($this->dontClassifyTheExcludedFn)($markPeriodGeneralAverageCalculateds);
            $totalStudentsClassed = count($markPeriodGeneralAverageCalculateds);
            foreach ($markPeriodGeneralAverageCalculateds as $index => $markPeriodGeneralAverageRankingCalculated) {
                $markPeriodGeneralAverageRankingCalculated->setRank($index + 1);
                $markPeriodGeneralAverageRankingCalculated->setTotalStudentsClassed($totalStudentsClassed);
            }

            $this->entityManager->flush();
        }
        return 'success';
    }

    // Fonction principale
    function validateOrDeleteMarks(string $case, mixed $element)
    {
        return $this->{'validateOrDelete' . $case . 'Marks'}($element);
    }

}