<?php

namespace App\Controller\School\Exam\EndProcessing\MarkValidation\Services\Annual\GetServices;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Operation\Annual\Course\MarkAnnualCourseCalculated;
use App\Entity\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCalculated;
use App\Entity\School\Exam\Operation\Annual\Module\MarkAnnualModuleCalculated;
use App\Entity\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculated;
use App\Entity\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculated;
use App\Entity\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculated;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Operation\Annual\Course\MarkAnnualCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Annual\Module\MarkAnnualModuleCalculatedRepository;
use App\Repository\School\Exam\Operation\Annual\Module\MarkAnnualModuleCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculatedRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;

class AnnualMarkPrimaryAndSecondaryValidationGetAllMarksCalculatedUtil
{
    public function __construct(
        private readonly StudentRegistration $student,
        private readonly array                                          $evaluationPeriods,
        private readonly EvaluationPeriodRepository                     $evaluationPeriodRepository,
        private readonly StudentCourseRegistrationRepository            $studentCourseRegistrationRepository,
        private readonly MarkSequenceCourseCalculatedRepository         $markSequenceCourseCalculatedRepository,
        private readonly MarkSequenceModuleCalculatedRepository         $markSequenceModuleCalculatedRepository,
        private readonly MarkSequenceGeneralAverageCalculatedRepository $markSequenceGeneralAverageCalculatedRepository,

        private readonly MarkPeriodCourseCalculatedRepository           $markPeriodCourseCalculatedRepository,
        private readonly MarkPeriodModuleCalculatedRepository           $markPeriodModuleCalculatedRepository,
        private readonly MarkPeriodModuleCalculationRelationRepository  $markPeriodModuleCalculationRelationRepository,
        private readonly MarkPeriodGeneralAverageCalculatedRepository   $markPeriodGeneralAverageCalculatedRepository,

        private readonly MarkAnnualCourseCalculatedRepository                          $markAnnualCourseCalculatedRepository,
        private readonly MarkAnnualModuleCalculatedRepository                          $markAnnualModuleCalculatedRepository,
        private readonly MarkAnnualModuleCalculationRelationRepository                 $markAnnualModuleCalculationRelationRepository,
        private readonly MarkAnnualGeneralAverageCalculatedRepository                  $markAnnualGeneralAverageCalculatedRepository,

    )
    {
    }

    public function getMarkAnnualCourseCalculatedFormatted(MarkAnnualCourseCalculated $markAnnualCourseCalculated): array
    {
        $markCalculatedFormatted = [];

//        $studentCourseRegistration = $markAnnualCourseCalculated->getStudentCourseRegistration();
        $classProgram = $markAnnualCourseCalculated->getClassProgram();
        $nameuvc = $classProgram->getNameuvc();
        $codeuvc = $classProgram->getCodeuvc();

        $mark = $markAnnualCourseCalculated->getMark();
        $coeff = $classProgram->getCoeff();
        $total = $markAnnualCourseCalculated->getTotal();

        $isCourseValidated = $markAnnualCourseCalculated->getIsCourseValidated();
        $validatedCredit = $consideredCredit = 0;
        $isModuleEliminated = $markAnnualCourseCalculated->getIsModuleEliminated();
        if ($isCourseValidated !== 'nv') {
            if ($isCourseValidated === 'v') $validatedCredit = $coeff;
            if (!$isModuleEliminated) $consideredCredit = $coeff;
        }

        $markGrade = $markAnnualCourseCalculated->getGrade();

        $markCalculatedFormatted['name'] = $nameuvc;

        foreach ($this->evaluationPeriods as $evaluationPeriodId => $sequences) {
            $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);
            $studentCourseRegistration = $this->studentCourseRegistrationRepository->findByStudentAndClassProgram($this->student,$classProgram,$evaluationPeriod);

            foreach ($sequences as $sequence) {
                $markSequenceCourseCalculated = $this->markSequenceCourseCalculatedRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $sequence,'evaluationPeriod'=>$evaluationPeriod]);
                $markCalculatedFormatted['ep' .$evaluationPeriodId. 'mark' . $sequence->getId()] = 'NC';
                if ($markSequenceCourseCalculated){
                    $sequenceMark = $markSequenceCourseCalculated->getMark();
                    $markCalculatedFormatted['ep' .$evaluationPeriodId. 'mark' . $sequence->getId()] = $sequenceMark;
                }
            }

            $markPeriodCourseCalculated = $this->markPeriodCourseCalculatedRepository->findOneBy(['studentCourseRegistration'=>$studentCourseRegistration]);
            $markCalculatedFormatted['ep' .$evaluationPeriodId] = 'NC';

            if ($markPeriodCourseCalculated){
                $markCalculatedFormatted['ep' .$evaluationPeriodId] = $markPeriodCourseCalculated->getMark();
            }
        }

        $markCalculatedFormatted['mark'] = $mark;
        $markCalculatedFormatted['coeff'] = $coeff;
        $markCalculatedFormatted['total'] = $total;
        $markCalculatedFormatted['validatedCredit'] = $validatedCredit;
        $markCalculatedFormatted['consideredCredit'] = $consideredCredit;
        $markCalculatedFormatted['courseStatus'] = $isCourseValidated;
        $markCalculatedFormatted['moduleStatus'] = $isModuleEliminated;
        $markCalculatedFormatted['description'] = $markGrade?->getDescription();
        $markCalculatedFormatted['grade'] = $markGrade?->getCode();
        $markCalculatedFormatted['gpa'] = $markGrade?->getGpa();
        $markCalculatedFormatted['averageGpa'] = $markGrade?->getGpa();
        $markCalculatedFormatted['isModule'] = false;
        $markCalculatedFormatted['isGeneralAverage'] = false;
        $markCalculatedFormatted['isNotModule'] = false;

        return $markCalculatedFormatted;
    }

    public function getMarkAnnualModuleCalculatedFormatted(MarkAnnualModuleCalculated|MarkAnnualGeneralAverageCalculated $markAnnualModuleCalculated)
    {
        $coeff = $markAnnualModuleCalculated->getTotalCredit();
        $total = $markAnnualModuleCalculated->getTotal();
        $validatedCredit = $markAnnualModuleCalculated->getTotalCreditValidated();
        $consideredCredit = $markAnnualModuleCalculated->getTotalCreditConsidered();

        $markGrade = $markAnnualModuleCalculated->getGrade();

        $averageGpa = $markAnnualModuleCalculated->getAverageGpa();

        $markCalculatedFormatted = [];

        $markCalculatedFormatted['coeff'] = $coeff;
        $markCalculatedFormatted['total'] = $total;
        $markCalculatedFormatted['validatedCredit'] = $validatedCredit;
        $markCalculatedFormatted['consideredCredit'] = $consideredCredit;
        $markCalculatedFormatted['moduleStatus'] = $markAnnualModuleCalculated->getIsEliminated();
        $markCalculatedFormatted['description'] = $markGrade?->getDescription();
        $markCalculatedFormatted['grade'] = $markGrade?->getCode();
        $markCalculatedFormatted['gpa'] = $markGrade?->getGpa();
        $markCalculatedFormatted['averageGpa'] = $averageGpa;
        $markCalculatedFormatted['isGeneralAverage'] = false;
        $markCalculatedFormatted['isNotModule'] = false;

        return $markCalculatedFormatted;
    }

    function getAllAnnualMarksCalculated(StudentRegistration $student)
    {
        $criteria = ['student' => $student];
        $criteriaModule = $criteria;
        $criteriaModule['module'] = null;

        $markAnnualCourseCalculateds = $this->markAnnualCourseCalculatedRepository->findBy($criteriaModule);
        $markAnnualModuleCalculateds = $this->markAnnualModuleCalculatedRepository->findBy($criteria);
//        dd($criteria);
        $markAnnualGeneralAverageCalculated = $this->markAnnualGeneralAverageCalculatedRepository->findOneBy($criteria);

        $allMarksCalculated = [];
//        dd($markAnnualModuleCalculateds,!empty($markAnnualModuleCalculateds));
        if (!empty($markAnnualModuleCalculateds)) {
            // Gestion des modules dans l'affichage
            foreach ($markAnnualModuleCalculateds as $markAnnualModuleCalculated) {
                $module = $markAnnualModuleCalculated->getModule();
                $criteriaModule['module'] = $module;

                $name = $module->getName();
                $mark = $markAnnualModuleCalculated->getMark();
                $markCalculatedFormatted = $this->getMarkAnnualModuleCalculatedFormatted($markAnnualModuleCalculated);

                $markCalculatedFormatted['name'] = $name;
                foreach ($this->evaluationPeriods as $evaluationPeriodId => $sequences) {
                    $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);

                    foreach ($sequences as $sequence) {
                        $markSequenceModuleCalculated = $this->markSequenceModuleCalculatedRepository->findOneBy(['student' => $student, 'module' => $module, 'sequence' => $sequence, 'evaluationPeriod' => $evaluationPeriod]);
                        $markCalculatedFormatted['ep' . $evaluationPeriodId . 'mark' . $sequence->getId()] = 'NC';
                        if ($markSequenceModuleCalculated) {
                            $sequenceMark = $markSequenceModuleCalculated->getMark();
                            $markCalculatedFormatted['ep' . $evaluationPeriodId . 'mark' . $sequence->getId()] = $sequenceMark;
                        }
                    }
                    $markPeriodModuleCalculated = $this->markPeriodModuleCalculatedRepository->findOneBy(['student' => $student, 'module' => $module, 'evaluationPeriod' => $evaluationPeriod]);
                    $markCalculatedFormatted['ep' .$evaluationPeriodId] = 'NC';

                    if ($markPeriodModuleCalculated){
                        $markCalculatedFormatted['ep' .$evaluationPeriodId] = $markPeriodModuleCalculated->getMark();
                    }
                }
                $markCalculatedFormatted['mark'] = $mark;

                $markCalculatedFormatted['isModule'] = true;
                $allMarksCalculated[] = $markCalculatedFormatted;

                $markAnnualModuleCalculationRelations = $this->markAnnualModuleCalculationRelationRepository->findBy($criteriaModule);
                foreach ($markAnnualModuleCalculationRelations as $markAnnualModuleCalculationRelation) {
                    $markAnnualCourseCalculated = $markAnnualModuleCalculationRelation->getMarkCourseCalculated();
                    $markCalculatedFormatted = $this->getMarkAnnualCourseCalculatedFormatted($markAnnualCourseCalculated);

                    $allMarksCalculated[] = $markCalculatedFormatted;
                }
            }
        }

        if (!empty($markAnnualCourseCalculateds)) {
            $markCalculatedFormatted = [];
            $markCalculatedFormatted['name'] = 'not module courses';
            $markCalculatedFormatted['mark'] = '';
            $markCalculatedFormatted['coeff'] = '';
            $markCalculatedFormatted['description'] = '';
            $markCalculatedFormatted['grade'] = '';
            $markCalculatedFormatted['gpa'] = '';
            $markCalculatedFormatted['averageGpa'] = '';
            $markCalculatedFormatted['isGeneralAverage'] = false;
            $markCalculatedFormatted['isModule'] = false;
            $markCalculatedFormatted['isNotModule'] = true;
            $allMarksCalculated[] = $markCalculatedFormatted;
            foreach ($markAnnualCourseCalculateds as $markAnnualCourseCalculated) {
                $allMarksCalculated[] = $this->getMarkAnnualCourseCalculatedFormatted($markAnnualCourseCalculated);
            }
        }

        $markCalculatedFormatted = $this->getMarkAnnualModuleCalculatedFormatted($markAnnualGeneralAverageCalculated);
        $markCalculatedFormatted['name'] = 'general average';
        foreach ($this->evaluationPeriods as $evaluationPeriodId => $sequences) {
            $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);

            foreach ($sequences as $sequence) {
            $markSequenceGeneralAverageCalculated = $this->markSequenceGeneralAverageCalculatedRepository->findOneBy(['student'=>$student, 'sequence' => $sequence,'evaluationPeriod' => $evaluationPeriod]);
            $markCalculatedFormatted['ep' . $evaluationPeriodId . 'mark' . $sequence->getId()] = 'NC';
            if ($markSequenceGeneralAverageCalculated){
                $sequenceAverage = $markSequenceGeneralAverageCalculated->getAverage();
                $markCalculatedFormatted['ep' . $evaluationPeriodId . 'mark' . $sequence->getId()] = $sequenceAverage;
                }
            }

            $markPeriodGeneralAverageCalculated = $this->markPeriodGeneralAverageCalculatedRepository->findOneBy(['student'=>$student,'evaluationPeriod' => $evaluationPeriod]);
            $markCalculatedFormatted['ep' . $evaluationPeriodId] = 'NC';
            if ($markPeriodGeneralAverageCalculated){
                $sequenceAverage = $markPeriodGeneralAverageCalculated->getAverage();
                $markCalculatedFormatted['ep' . $evaluationPeriodId] = $sequenceAverage;
            }
        }
        $markCalculatedFormatted['mark'] = $markAnnualGeneralAverageCalculated->getAverage();

        $markCalculatedFormatted['isModule'] = false;
        $markCalculatedFormatted['isGeneralAverage'] = true;
        $markCalculatedFormatted['isEliminated'] = $markAnnualGeneralAverageCalculated->getIsEliminated();

        $allMarksCalculated[] = $markCalculatedFormatted;

        /*$markCalculatedFormatted = [];
        $markCalculatedFormatted['numberOfAttendedCourses'] = $markPeriodGeneralAverageCalculated->getNumberOfAttendedCourses();
        $markCalculatedFormatted['numberOfComposedCourses'] = $markPeriodGeneralAverageCalculated->getNumberOfComposedCourses();
        $markCalculatedFormatted['totalOfCreditsAttended'] = $markPeriodGeneralAverageCalculated->getTotalOfCreditsAttended();
        $markCalculatedFormatted['totalOfCreditsComposed'] = $markPeriodGeneralAverageCalculated->getTotalOfCreditsComposed();
        $markCalculatedFormatted['percentageSubjectNumber'] = $markPeriodGeneralAverageCalculated->getPercentageSubjectNumber();
        $markCalculatedFormatted['percentageTotalCoefficient'] = $markPeriodGeneralAverageCalculated->getPercentageTotalCoefficient();
        $markCalculatedFormatted['isClassed'] = $markPeriodGeneralAverageCalculated->getIsClassed();

        , 'classificationInfos' => $markCalculatedFormatted
        */

        return $allMarksCalculated;
    }

    function getAllMarksCalculated(StudentRegistration $student)
    {
        $allAnnualMarksCalculated = $this->getAllAnnualMarksCalculated($student);

        $result = [];

        $markAnnualGeneralAverageCalculated = $this->markAnnualGeneralAverageCalculatedRepository->findOneBy(['student'=>$student]);

        $markCalculatedFormatted = [];
        $markCalculatedFormatted['numberOfAttendedCourses'] = $markAnnualGeneralAverageCalculated->getNumberOfAttendedCourses();
        $markCalculatedFormatted['numberOfComposedCourses'] = $markAnnualGeneralAverageCalculated->getNumberOfComposedCourses();
        $markCalculatedFormatted['totalOfCreditsAttended'] = $markAnnualGeneralAverageCalculated->getTotalOfCreditsAttended();
        $markCalculatedFormatted['totalOfCreditsComposed'] = $markAnnualGeneralAverageCalculated->getTotalOfCreditsComposed();
        $markCalculatedFormatted['percentageSubjectNumber'] = $markAnnualGeneralAverageCalculated->getPercentageSubjectNumber();
        $markCalculatedFormatted['percentageTotalCoefficient'] = $markAnnualGeneralAverageCalculated->getPercentageTotalCoefficient();
        $markCalculatedFormatted['isClassed'] = $markAnnualGeneralAverageCalculated->getIsClassed();

        $classificationInfos = $markCalculatedFormatted;

        $markCalculatedFormatted = $this->getMarkAnnualModuleCalculatedFormatted($markAnnualGeneralAverageCalculated);
        $markCalculatedFormatted['name'] = 'general average';
        $markCalculatedFormatted['isPromoted'] = $markAnnualGeneralAverageCalculated->getIsPromoted();
        $markCalculatedFormatted['mark'] = $markAnnualGeneralAverageCalculated->getAverage();

        $generalAverageResult = $markCalculatedFormatted;

        // Recuperation des periodes d'evaluation et des sequences associees
        $evaluationPeriodsDatas = [] ;
        foreach ($this->evaluationPeriods as $evaluationPeriodId => $sequences) {
            $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);
            $evaluationPeriodData['evaluationPeriod'] = $evaluationPeriod;
            $evaluationPeriodData['sequences'] = $sequences;
            $evaluationPeriodsDatas[] = $evaluationPeriodData;
        }

        $result['allPeriodMarksCalculated'] =
            ['allMarksCalculated'=>$allAnnualMarksCalculated ,
            'evaluationPeriodsDatas'=>$evaluationPeriodsDatas
        ]
        ;
        $result['classificationInfos'] = $classificationInfos;
        $result['generalAverageResult'] = $generalAverageResult;


        return $result;
    }
}