<?php

namespace App\Controller\School\Exam\EndProcessing\MarkValidation\Services\Annual;

use App\Entity\School\Exam\Operation\Annual\Course\MarkAnnualCourseSequenceCalculationRelation;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Operation\Annual\Course\MarkAnnualCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Annual\Course\MarkAnnualCoursePeriodCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Annual\Course\MarkAnnualCourseSequenceCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCourseTernaryCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAveragePeriodCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageSequenceCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Annual\Module\MarkAnnualModuleCalculatedRepository;
use App\Repository\School\Exam\Operation\Annual\Module\MarkAnnualModuleCalculationRelationRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use Doctrine\ORM\EntityManagerInterface;

class AnnualMarkValidationValidateDeleteUtil
{

    private \Closure $dontClassifyTheExcludedFn;

    public function __construct(
        protected bool                                   $calculateSubjectsRanks,
        protected bool                                   $dontClassifyTheExcluded,
        private string                                                                                  $validateMethod,
        private string                                                                                  $schoolSystem,
        private readonly ClassProgramRepository                                                         $classProgramRepository,
        private readonly MarkAnnualCourseCalculatedRepository                                           $markAnnualCourseCalculatedRepository,
        private readonly MarkAnnualCourseSequenceCalculationRelationRepository                          $markAnnualCourseSequenceCalculationRelationRepository,
        private readonly MarkAnnualCoursePeriodCalculationRelationRepository                            $markAnnualCoursePeriodCalculationRelationRepository,
        private readonly MarkAnnualModuleCalculatedRepository                                           $markAnnualModuleCalculatedRepository,
        private readonly MarkAnnualModuleCalculationRelationRepository                                  $markAnnualModuleCalculationRelationRepository,
        private readonly MarkAnnualGeneralAverageCalculatedRepository                                   $markAnnualGeneralAverageCalculatedRepository,
        private readonly MarkAnnualGeneralAverageSequenceCalculationRelationRepository                  $markAnnualGeneralAverageSequenceCalculationRelationRepository,
        private readonly MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelationRepository | MarkAnnualGeneralAverageCourseTernaryCalculationRelationRepository $markAnnualGeneralAverageCourseCalculationRelationRepository,
        private readonly MarkAnnualGeneralAveragePeriodCalculationRelationRepository                    $markAnnualGeneralAveragePeriodCalculationRelationRepository,

        private readonly EntityManagerInterface                                                         $entityManager
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
        $criteria = ['student' => $student];

        $markAnnualCourseSequenceCalculationRelations = $this->markAnnualCourseSequenceCalculationRelationRepository->findBy($criteria);
        $markAnnualCoursePeriodCalculationRelations = $this->markAnnualCoursePeriodCalculationRelationRepository->findBy($criteria);
        $markAnnualModuleCalculationRelations = $this->markAnnualModuleCalculationRelationRepository->findBy($criteria);

        $markAnnualCourseCalculateds = $this->markAnnualCourseCalculatedRepository->findBy($criteria);
        $markAnnualModuleCalculateds = $this->markAnnualModuleCalculatedRepository->findBy($criteria);


        $markAnnualGeneralAverageSequenceCalculationRelations = $this->markAnnualGeneralAverageSequenceCalculationRelationRepository->findBy($criteria);
        $markAnnualGeneralAverageCourseCalculationRelations = $this->markAnnualGeneralAverageCourseCalculationRelationRepository->findBy($criteria);
        $markAnnualGeneralAveragePeriodCalculationRelations = $this->markAnnualGeneralAveragePeriodCalculationRelationRepository->findBy($criteria);

        $markAnnualGeneralAverageCalculateds = $this->markAnnualGeneralAverageCalculatedRepository->findBy($criteria);

        foreach ($markAnnualCourseSequenceCalculationRelations as $markAnnualCalculationRelation) {
            $markAnnualCalculationRelation->setIsValidated($validate);
        }
        foreach ($markAnnualCoursePeriodCalculationRelations as $markAnnualCalculationRelation) {
            $markAnnualCalculationRelation->setIsValidated($validate);
        }
        foreach ($markAnnualModuleCalculationRelations as $markAnnualCalculationRelation) {
            $markAnnualCalculationRelation->setIsValidated($validate);
        }

        foreach ($markAnnualGeneralAverageSequenceCalculationRelations as $markAnnualCalculationRelation) {
                $markAnnualCalculationRelation->setIsValidated($validate);
            }

            foreach ($markAnnualGeneralAverageCourseCalculationRelations as $markAnnualCalculationRelation) {
                $markAnnualCalculationRelation->setIsValidated($validate);
            }

           foreach ($markAnnualGeneralAveragePeriodCalculationRelations as $markAnnualCalculationRelation) {
                $markAnnualCalculationRelation->setIsValidated($validate);
            }

            foreach ($markAnnualGeneralAverageCalculateds as $markAnnualCalculated) {
                $markAnnualCalculated->setIsValidated($validate);
            }

        foreach ($markAnnualCourseCalculateds as $markAnnualCalculated) {
            $markAnnualCalculated->setIsValidated($validate);
        }
        foreach ($markAnnualModuleCalculateds as $markAnnualCalculated) {
            $markAnnualCalculated->setIsValidated($validate);
        }

        $this->entityManager->flush();

        return 'success';
    }

    function unvalidateStudentMarks(StudentRegistration $student){
        return $this->validateStudentMarks($student, false);
    }

    function deleteStudentMarks(StudentRegistration $student)
    {
        $criteria = ['student' => $student];
        $markAnnualCourseSequenceCalculationRelations = $this->markAnnualCourseSequenceCalculationRelationRepository->findBy($criteria);
        $markAnnualCoursePeriodCalculationRelations = $this->markAnnualCoursePeriodCalculationRelationRepository->findBy($criteria);
        $markAnnualModuleCalculationRelations = $this->markAnnualModuleCalculationRelationRepository->findBy($criteria);

        $markAnnualCourseCalculateds = $this->markAnnualCourseCalculatedRepository->findBy($criteria);
        $markAnnualModuleCalculateds = $this->markAnnualModuleCalculatedRepository->findBy($criteria);

        $markAnnualGeneralAverageSequenceCalculationRelations = $this->markAnnualGeneralAverageSequenceCalculationRelationRepository->findBy($criteria);
        $markAnnualGeneralAverageCourseCalculationRelations = $this->markAnnualGeneralAverageCourseCalculationRelationRepository->findBy($criteria);
        $markAnnualGeneralAveragePeriodCalculationRelations = $this->markAnnualGeneralAveragePeriodCalculationRelationRepository->findBy($criteria);

       $markAnnualGeneralAverageCalculateds = $this->markAnnualGeneralAverageCalculatedRepository->findBy($criteria);

        foreach ($markAnnualCourseSequenceCalculationRelations as $markAnnualCalculationRelation) {
            if (!$markAnnualCalculationRelation->getIsValidated()) $this->entityManager->remove($markAnnualCalculationRelation);
        }
        foreach ($markAnnualCoursePeriodCalculationRelations as $markAnnualCalculationRelation) {
            if (!$markAnnualCalculationRelation->getIsValidated()) $this->entityManager->remove($markAnnualCalculationRelation);
        }
        foreach ($markAnnualModuleCalculationRelations as $markAnnualCalculationRelation) {
            if (!$markAnnualCalculationRelation->getIsValidated()) $this->entityManager->remove($markAnnualCalculationRelation);
        }
        foreach ($markAnnualGeneralAverageSequenceCalculationRelations as $markAnnualCalculationRelation) {
                if (!$markAnnualCalculationRelation->getIsValidated()) $this->entityManager->remove($markAnnualCalculationRelation);
            }
            foreach ($markAnnualGeneralAverageCourseCalculationRelations as $markAnnualCalculationRelation) {
                if (!$markAnnualCalculationRelation->getIsValidated()) $this->entityManager->remove($markAnnualCalculationRelation);
            }
            foreach ($markAnnualGeneralAveragePeriodCalculationRelations as $markAnnualCalculationRelation) {
                if (!$markAnnualCalculationRelation->getIsValidated()) $this->entityManager->remove($markAnnualCalculationRelation);
            }

            foreach ($markAnnualGeneralAverageCalculateds as $markAnnualCalculated) {
                if (!$markAnnualCalculated->getIsValidated()) $this->entityManager->remove($markAnnualCalculated);
            }

        foreach ($markAnnualCourseCalculateds as $markAnnualCalculated) {
            if (!$markAnnualCalculated->getIsValidated()) $this->entityManager->remove($markAnnualCalculated);
        }
        foreach ($markAnnualModuleCalculateds as $markAnnualCalculated) {
            if (!$markAnnualCalculated->getIsValidated()) $this->entityManager->remove($markAnnualCalculated);
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
            if ($this->calculateSubjectsRanks && $this->schoolSystem === 'primary') {
                $classProgramIds = $this->markAnnualCourseCalculatedRepository->getClassPrograms($class);
                $classPrograms = array_map(fn(int $classProgramId) => $this->classProgramRepository->find($classProgramId), array_column($classProgramIds, 'classProgramId'));
                if ($this->dontClassifyTheExcluded) {
                    foreach ($classPrograms as $classProgram) {
                        $markAnnualCourseRankingCalculateds = $this->markAnnualCourseCalculatedRepository->findBy(['classProgram' => $classProgram], ['mark' => 'DESC']);
                        $markAnnualCourseRankingCalculateds = ($this->dontClassifyTheExcludedFn)($markAnnualCourseRankingCalculateds);
                        $totalCourseStudentsRegistered = count($markAnnualCourseRankingCalculateds);
                        foreach ($markAnnualCourseRankingCalculateds as $index => $markAnnualCourseRankingCalculated) {
                            $markAnnualCourseRankingCalculated->setRank($index + 1);
                            $markAnnualCourseRankingCalculated->setTotalCourseStudentsRegistered($totalCourseStudentsRegistered);
                        }
                    }
                }
                else {
                    foreach ($classPrograms as $classProgram) {
                        $markAnnualCourseRankingCalculateds = $this->markAnnualCourseCalculatedRepository->findBy(['classProgram' => $classProgram], ['mark' => 'DESC']);
                        $totalCourseStudentsRegistered = count($markAnnualCourseRankingCalculateds);
                        foreach ($markAnnualCourseRankingCalculateds as $index => $markAnnualCourseRankingCalculated) {
                            $markAnnualCourseRankingCalculated->setRank($index + 1);
                            $markAnnualCourseRankingCalculated->setTotalCourseStudentsRegistered($totalCourseStudentsRegistered);
                        }
                    }
                }
            }

            // Mettre les rangs sur les moyennes generales
            $markAnnualGeneralAverageCalculateds = $this->markAnnualGeneralAverageCalculatedRepository->findBy(['class' => $class, 'isClassed' => true], ['average' => 'DESC']);
            if ($this->dontClassifyTheExcluded) $markAnnualGeneralAverageCalculateds = ($this->dontClassifyTheExcludedFn)($markAnnualGeneralAverageCalculateds);
            $totalStudentsClassed = count($markAnnualGeneralAverageCalculateds);
            foreach ($markAnnualGeneralAverageCalculateds as $index => $markAnnualGeneralAverageRankingCalculated) {
                $markAnnualGeneralAverageRankingCalculated->setRank($index + 1);
                $markAnnualGeneralAverageRankingCalculated->setTotalStudentsClassed($totalStudentsClassed);
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