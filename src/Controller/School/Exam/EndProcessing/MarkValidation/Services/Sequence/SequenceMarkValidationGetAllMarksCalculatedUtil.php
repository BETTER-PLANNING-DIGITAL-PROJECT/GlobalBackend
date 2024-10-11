<?php

namespace App\Controller\School\Exam\EndProcessing\MarkValidation\Services\Sequence;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculated;
use App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculated;
use App\Entity\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculated;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculationRelationRepository;

class SequenceMarkValidationGetAllMarksCalculatedUtil
{

    public function __construct(
        private readonly MarkSequenceCourseCalculatedRepository                  $markSequenceCourseCalculatedRepository,

        private readonly MarkSequenceModuleCalculatedRepository                  $markSequenceModuleCalculatedRepository,
        private readonly MarkSequenceModuleCalculationRelationRepository         $markSequenceModuleCalculationRelationRepository,

        private readonly MarkSequenceGeneralAverageCalculatedRepository          $markSequenceGeneralAverageCalculatedRepository
    )
    {

    }

    public function getMarkSequenceCourseCalculatedFormatted(MarkSequenceCourseCalculated $markSequenceCourseCalculated): array
    {
        $markCalculatedFormatted = [];

        $classProgram = $markSequenceCourseCalculated->getClassProgram();
        $nameuvc = $classProgram->getNameuvc();

        $mark = $markSequenceCourseCalculated->getMark();
        $coeff = $classProgram->getCoeff();

        $isCourseValidated = $markSequenceCourseCalculated->getIsCourseValidated();
        $validatedCredit = $isCourseValidated ? $coeff : 0;

        $markGrade = $markSequenceCourseCalculated->getGrade();


        $markCalculatedFormatted['name'] = $nameuvc;
        $markCalculatedFormatted['mark'] = $mark;
        $markCalculatedFormatted['coeff'] = $coeff;
        $markCalculatedFormatted['validatedCredit'] = $validatedCredit;
        $markCalculatedFormatted['description'] = $markGrade?->getDescription();
        $markCalculatedFormatted['grade'] = $markGrade?->getCode();
        $markCalculatedFormatted['gpa'] = $markGrade?->getGpa();
        $markCalculatedFormatted['averageGpa'] = $markGrade?->getGpa();
        $markCalculatedFormatted['isModule'] = false;
        $markCalculatedFormatted['isGeneralAverage'] = false;
        $markCalculatedFormatted['isNotModule'] = false;

        return $markCalculatedFormatted;
    }

    public function getMarkSequenceModuleCalculatedFormatted(MarkSequenceModuleCalculated | MarkSequenceGeneralAverageCalculated $markSequenceModuleCalculated)
    {
        $coeff = $markSequenceModuleCalculated->getTotalCredit();
        $validatedCredit = $markSequenceModuleCalculated->getTotalCreditValidated();

        $markGrade = $markSequenceModuleCalculated->getGrade();

        $averageGpa = $markSequenceModuleCalculated->getAverageGpa();

        $markCalculatedFormatted = [];

        $markCalculatedFormatted['coeff'] = $coeff;
        $markCalculatedFormatted['validatedCredit'] = $validatedCredit;
        $markCalculatedFormatted['description'] = $markGrade?->getDescription();
        $markCalculatedFormatted['grade'] = $markGrade?->getCode();
        $markCalculatedFormatted['gpa'] = $markGrade?->getGpa();
        $markCalculatedFormatted['averageGpa'] = $averageGpa;
        $markCalculatedFormatted['isGeneralAverage'] = false;
        $markCalculatedFormatted['isNotModule'] = false;

        return $markCalculatedFormatted;
    }
    function getAllMarksCalculated(StudentRegistration $student, EvaluationPeriod $evaluationPeriod, Sequence $sequence)
    {
        $criteria = ['student' => $student, 'evaluationPeriod' => $evaluationPeriod, 'sequence' => $sequence];
        $criteriaModule = $criteria;
        $criteriaModule['module']=null;

        $markSequenceCourseCalculateds = $this->markSequenceCourseCalculatedRepository->findBy($criteriaModule);
        $markSequenceModuleCalculateds = $this->markSequenceModuleCalculatedRepository->findBy($criteria);
//        dd($criteria);
        $markSequenceGeneralAverageCalculated = $this->markSequenceGeneralAverageCalculatedRepository->findOneBy($criteria);

        $allMarksCalculated = [];
//        dd($markSequenceModuleCalculateds,!empty($markSequenceModuleCalculateds));
        if (!empty($markSequenceModuleCalculateds)) {
            // Gestion des modules dans l'affichage
            foreach ($markSequenceModuleCalculateds as $markSequenceModuleCalculated) {
                $module = $markSequenceModuleCalculated->getModule();
                $criteriaModule['module'] = $module;

                $name = $module->getName();
                $mark = $markSequenceModuleCalculated->getMark();
                $markCalculatedFormatted = $this->getMarkSequenceModuleCalculatedFormatted($markSequenceModuleCalculated);

                $markCalculatedFormatted['name'] = $name;
                $markCalculatedFormatted['mark'] = $mark;
                $markCalculatedFormatted['isModule'] = true;
                $allMarksCalculated[] = $markCalculatedFormatted;

                $markSequenceModuleCalculationRelations = $this->markSequenceModuleCalculationRelationRepository->findBy($criteriaModule);
                foreach ($markSequenceModuleCalculationRelations as $markSequenceModuleCalculationRelation) {
                    $markSequenceCourseCalculated = $markSequenceModuleCalculationRelation->getMarkCourseCalculated();
                    $markCalculatedFormatted = $this->getMarkSequenceCourseCalculatedFormatted($markSequenceCourseCalculated);

                    $allMarksCalculated[] = $markCalculatedFormatted;
                }
            }
        }

        if (!empty($markSequenceCourseCalculateds)) {
            $markCalculatedFormatted = [];
            $markCalculatedFormatted['name'] = 'not module courses';
            $markCalculatedFormatted['mark'] = '';
            $markCalculatedFormatted['coeff'] = '';
            $markCalculatedFormatted['validatedCredit'] = '';
            $markCalculatedFormatted['description'] = '';
            $markCalculatedFormatted['grade'] = '';
            $markCalculatedFormatted['gpa'] = '';
            $markCalculatedFormatted['averageGpa'] = '';
            $markCalculatedFormatted['isGeneralAverage'] = false;
            $markCalculatedFormatted['isModule'] = false;
            $markCalculatedFormatted['isNotModule'] = true;
            $allMarksCalculated[] = $markCalculatedFormatted;
            foreach ($markSequenceCourseCalculateds as $markSequenceCourseCalculated) {
                $allMarksCalculated[] = $this->getMarkSequenceCourseCalculatedFormatted($markSequenceCourseCalculated);
            }
        }

        $markCalculatedFormatted = $this->getMarkSequenceModuleCalculatedFormatted($markSequenceGeneralAverageCalculated);
        $markCalculatedFormatted['name'] = 'general average';
        $markCalculatedFormatted['mark'] = $markSequenceGeneralAverageCalculated->getAverage();

        $markCalculatedFormatted['isModule'] = false;
        $markCalculatedFormatted['isGeneralAverage'] = true;
        $markCalculatedFormatted['isEliminated'] = $markSequenceGeneralAverageCalculated->getIsEliminated();

        $allMarksCalculated[] = $markCalculatedFormatted;

        $classificationInfos = [];
        $classificationInfos['numberOfAttendedCourses'] = $markSequenceGeneralAverageCalculated->getNumberOfAttendedCourses();
        $classificationInfos['numberOfComposedCourses'] = $markSequenceGeneralAverageCalculated->getNumberOfComposedCourses();
        $classificationInfos['totalOfCreditsAttended'] = $markSequenceGeneralAverageCalculated->getTotalOfCreditsAttended();
        $classificationInfos['totalOfCreditsComposed'] = $markSequenceGeneralAverageCalculated->getTotalOfCreditsComposed();
        $classificationInfos['percentageSubjectNumber'] = $markSequenceGeneralAverageCalculated->getPercentageSubjectNumber();
        $classificationInfos['percentageTotalCoefficient'] = $markSequenceGeneralAverageCalculated->getPercentageTotalCoefficient();
        $classificationInfos['isClassed'] = $markSequenceGeneralAverageCalculated->getIsClassed();


        // Informations pour les releves de notes
        $transcriptInfos = ['student'=>$student->getStudent(),'evaluationPeriod'=>$evaluationPeriod,'sequence'=>$sequence,'class'=>$student->getClasse(),'year'=>$student->getCurrentYear()];
        return ['allMarksCalculated'=>$allMarksCalculated,'classificationInfos'=>$classificationInfos,'transcriptInfos'=>$transcriptInfos];
    }
}