<?php

namespace App\Controller\School\Exam\EndProcessing\MarkValidation\Services\Period;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculated;
use App\Entity\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculated;
use App\Entity\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculated;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculatedRepository;

class PeriodMarkValidationGetAllMarksCalculatedUtil
{
    public function __construct(
        private EvaluationPeriod                                        $evaluationPeriod,
        private readonly array                                          $sequences,
        private readonly MarkSequenceCourseCalculatedRepository         $markSequenceCourseCalculatedRepository,
        private readonly MarkSequenceModuleCalculatedRepository         $markSequenceModuleCalculatedRepository,
        private readonly MarkSequenceGeneralAverageCalculatedRepository $markSequenceGeneralAverageCalculatedRepository,
        private readonly MarkPeriodCourseCalculatedRepository           $markPeriodCourseCalculatedRepository,

        private readonly MarkPeriodModuleCalculatedRepository           $markPeriodModuleCalculatedRepository,
        private readonly MarkPeriodModuleCalculationRelationRepository  $markPeriodModuleCalculationRelationRepository,

        private readonly MarkPeriodGeneralAverageCalculatedRepository   $markPeriodGeneralAverageCalculatedRepository,

    )
    {
    }

    public function getMarkPeriodCourseCalculatedFormatted(MarkPeriodCourseCalculated $markPeriodCourseCalculated): array
    {
        $markCalculatedFormatted = [];

        $studentCourseRegistration = $markPeriodCourseCalculated->getStudentCourseRegistration();
        $classProgram = $markPeriodCourseCalculated->getClassProgram();
        $nameuvc = $classProgram->getNameuvc();

        $mark = $markPeriodCourseCalculated->getMark();
        $coeff = $classProgram->getCoeff();

        $isCourseValidated = $markPeriodCourseCalculated->getIsCourseValidated();
        $validatedCredit = $consideredCredit = 0;
        $isModuleEliminated = $markPeriodCourseCalculated->getIsModuleEliminated();
        if ($isCourseValidated !== 'nv') {
            if ($isCourseValidated === 'v') $validatedCredit = $coeff;
            if (!$isModuleEliminated) $consideredCredit = $coeff;
        }

        $markGrade = $markPeriodCourseCalculated->getGrade();

        $markCalculatedFormatted['name'] = $nameuvc;

        foreach ($this->sequences as $sequence) {
            $markSequenceCourseCalculated = $this->markSequenceCourseCalculatedRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $sequence,'evaluationPeriod'=>$this->evaluationPeriod]);
            $markCalculatedFormatted['mark' . $sequence->getId()] = 'NC';
            if ($markSequenceCourseCalculated){
                $sequenceMark = $markSequenceCourseCalculated->getMark();
                $markCalculatedFormatted['mark' . $sequence->getId()] = $sequenceMark;
            }
        }

        $markCalculatedFormatted['mark'] = $mark;
        $markCalculatedFormatted['coeff'] = $coeff;
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

    public function getMarkPeriodModuleCalculatedFormatted(MarkPeriodModuleCalculated|MarkPeriodGeneralAverageCalculated $markPeriodModuleCalculated)
    {
        $coeff = $markPeriodModuleCalculated->getTotalCredit();
        $validatedCredit = $markPeriodModuleCalculated->getTotalCreditValidated();
        $consideredCredit = $markPeriodModuleCalculated->getTotalCreditConsidered();

        $markGrade = $markPeriodModuleCalculated->getGrade();

        $averageGpa = $markPeriodModuleCalculated->getAverageGpa();

        $markCalculatedFormatted = [];

        $markCalculatedFormatted['coeff'] = $coeff;
        $markCalculatedFormatted['validatedCredit'] = $validatedCredit;
        $markCalculatedFormatted['consideredCredit'] = $consideredCredit;
        $markCalculatedFormatted['moduleStatus'] = $markPeriodModuleCalculated->getIsEliminated();
        $markCalculatedFormatted['description'] = $markGrade?->getDescription();
        $markCalculatedFormatted['grade'] = $markGrade?->getCode();
        $markCalculatedFormatted['gpa'] = $markGrade?->getGpa();
        $markCalculatedFormatted['averageGpa'] = $averageGpa;
        $markCalculatedFormatted['isGeneralAverage'] = false;
        $markCalculatedFormatted['isNotModule'] = false;

        return $markCalculatedFormatted;
    }

    function getAllMarksCalculated(StudentRegistration $student, EvaluationPeriod $evaluationPeriod)
    {
        $criteria = ['student' => $student, 'evaluationPeriod' => $evaluationPeriod];
        $criteriaModule = $criteria;
        $criteriaModule['module'] = null;

        $markPeriodCourseCalculateds = $this->markPeriodCourseCalculatedRepository->findBy($criteriaModule);
        $markPeriodModuleCalculateds = $this->markPeriodModuleCalculatedRepository->findBy($criteria);
//        dd($criteria);
        $markPeriodGeneralAverageCalculated = $this->markPeriodGeneralAverageCalculatedRepository->findOneBy($criteria);

        $allMarksCalculated = [];
//        dd($markPeriodModuleCalculateds,!empty($markPeriodModuleCalculateds));
        if (!empty($markPeriodModuleCalculateds)) {
            // Gestion des modules dans l'affichage
            foreach ($markPeriodModuleCalculateds as $markPeriodModuleCalculated) {
                $module = $markPeriodModuleCalculated->getModule();
                $criteriaModule['module'] = $module;

                $name = $module->getName();
                $mark = $markPeriodModuleCalculated->getMark();
                $markCalculatedFormatted = $this->getMarkPeriodModuleCalculatedFormatted($markPeriodModuleCalculated);

                $markCalculatedFormatted['name'] = $name;
                foreach ($this->sequences as $sequence) {
                    $markSequenceModuleCalculated = $this->markSequenceModuleCalculatedRepository->findOneBy(['student'=>$student,'module' => $module, 'sequence' => $sequence,'evaluationPeriod' => $evaluationPeriod]);
                    $markCalculatedFormatted['mark' . $sequence->getId()] = 'NC';
                    if ($markSequenceModuleCalculated){
                        $sequenceMark = $markSequenceModuleCalculated->getMark();
                        $markCalculatedFormatted['mark' . $sequence->getId()] = $sequenceMark;
                    }
                }
                $markCalculatedFormatted['mark'] = $mark;

                $markCalculatedFormatted['isModule'] = true;
                $allMarksCalculated[] = $markCalculatedFormatted;

                $markPeriodModuleCalculationRelations = $this->markPeriodModuleCalculationRelationRepository->findBy($criteriaModule);
                foreach ($markPeriodModuleCalculationRelations as $markPeriodModuleCalculationRelation) {
                    $markPeriodCourseCalculated = $markPeriodModuleCalculationRelation->getMarkCourseCalculated();
                    $markCalculatedFormatted = $this->getMarkPeriodCourseCalculatedFormatted($markPeriodCourseCalculated);

                    $allMarksCalculated[] = $markCalculatedFormatted;
                }
            }
        }

        if (!empty($markPeriodCourseCalculateds)) {
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
            foreach ($markPeriodCourseCalculateds as $markPeriodCourseCalculated) {
                $markCalculatedFormatted = $this->getMarkPeriodCourseCalculatedFormatted($markPeriodCourseCalculated);
                $markCalculatedFormatted['isNotModuleCourse'] = true;
                $allMarksCalculated[] = $markCalculatedFormatted;

            }
        }

        $markCalculatedFormatted = $this->getMarkPeriodModuleCalculatedFormatted($markPeriodGeneralAverageCalculated);
        $markCalculatedFormatted['name'] = 'general average';
        foreach ($this->sequences as $sequence) {
            $markSequenceGeneralAverageCalculated = $this->markSequenceGeneralAverageCalculatedRepository->findOneBy(['student'=>$student, 'sequence' => $sequence,'evaluationPeriod' => $evaluationPeriod]);
            $markCalculatedFormatted['mark' . $sequence->getId()] = 'NC';
            if ($markSequenceGeneralAverageCalculated){
                $sequenceAverage = $markSequenceGeneralAverageCalculated->getAverage();
                $markCalculatedFormatted['mark' . $sequence->getId()] = $sequenceAverage;
            }
        }
        $markCalculatedFormatted['mark'] = $markPeriodGeneralAverageCalculated->getAverage();

        $markCalculatedFormatted['isModule'] = false;
        $markCalculatedFormatted['isGeneralAverage'] = true;
        $markCalculatedFormatted['isEliminated'] = $markPeriodGeneralAverageCalculated->getIsEliminated();

        $allMarksCalculated[] = $markCalculatedFormatted;

        $markCalculatedFormatted = [];
        $markCalculatedFormatted['numberOfAttendedCourses'] = $markPeriodGeneralAverageCalculated->getNumberOfAttendedCourses();
        $markCalculatedFormatted['numberOfComposedCourses'] = $markPeriodGeneralAverageCalculated->getNumberOfComposedCourses();
        $markCalculatedFormatted['totalOfCreditsAttended'] = $markPeriodGeneralAverageCalculated->getTotalOfCreditsAttended();
        $markCalculatedFormatted['totalOfCreditsComposed'] = $markPeriodGeneralAverageCalculated->getTotalOfCreditsComposed();
        $markCalculatedFormatted['percentageSubjectNumber'] = $markPeriodGeneralAverageCalculated->getPercentageSubjectNumber();
        $markCalculatedFormatted['percentageTotalCoefficient'] = $markPeriodGeneralAverageCalculated->getPercentageTotalCoefficient();
        $markCalculatedFormatted['isClassed'] = $markPeriodGeneralAverageCalculated->getIsClassed();

        return ['allMarksCalculated' => $allMarksCalculated, 'classificationInfos' => $markCalculatedFormatted, 'sequences'=>$this->sequences];
    }
}