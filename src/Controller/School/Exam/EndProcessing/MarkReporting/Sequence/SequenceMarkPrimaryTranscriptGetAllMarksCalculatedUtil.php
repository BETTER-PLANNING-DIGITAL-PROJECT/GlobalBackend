<?php

namespace App\Controller\School\Exam\EndProcessing\MarkReporting\Sequence;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculated;
use App\Entity\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculated;
use App\Entity\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculated;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Configuration\MarkGradeRepository;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculationRelationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Program\TeacherCourseRegistrationRepository;

class SequenceMarkPrimaryTranscriptGetAllMarksCalculatedUtil
{
    public function __construct(
        // Configurations
//        private bool $displaySequencesInBulletins,
        private bool $displayNumberOfRows,
        private bool $displayNumberOfRowsOnCourses,
        private bool $displayTheClassSizeInRows,
        private bool $calculateSubjectsRanks,
        private bool $displayMarksNotEnteredInTheBulletin,
        private bool $showPhotoOnSummary,

        // Attributs principaux
        private int                                                     $classHeadcount,
        private EvaluationPeriod $evaluationPeriod,
        private Sequence $sequence,
        private readonly array $previousSequences,
        private readonly array $previousEvaluationPeriods,
        private readonly MarkGradeRepository $markGradeRepository,
        private readonly MarkSequenceCourseCalculatedRepository         $markSequenceCourseCalculatedRepository,
        private readonly MarkSequenceModuleCalculatedRepository         $markSequenceModuleCalculatedRepository,
        private readonly MarkSequenceModuleCalculationRelationRepository  $markSequenceModuleCalculationRelationRepository,
        private readonly MarkSequenceGeneralAverageCalculatedRepository $markSequenceGeneralAverageCalculatedRepository,

        private readonly StudentRegistrationRepository $studentRegistrationRepository,
        private readonly TeacherCourseRegistrationRepository $teacherCourseRegistrationRepository
    )
    {
    }

    public function getMarkPeriodCourseCalculatedFormatted(MarkPeriodCourseCalculated $markPeriodCourseCalculated): array
    {
        $markCalculatedFormatted = [];

        $studentCourseRegistration = $markPeriodCourseCalculated->getStudentCourseRegistration();
        $classProgram = $markPeriodCourseCalculated->getClassProgram();
        $nameuvc = $classProgram->getNameuvc();
        $codeuvc = $classProgram->getCodeuvc();

        $mark = $markPeriodCourseCalculated->getMark();
        $markShowed = $mark;
        if ($mark === null){
            $markShowed = $this->displayMarksNotEnteredInTheBulletin ? 'X' : '';
        }

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
        $markCalculatedFormatted['code'] = $codeuvc;

        if ($this->displaySequencesInBulletins) {
            foreach ($this->sequences as $sequence) {
                $markSequenceCourseCalculated = $this->markSequenceCourseCalculatedRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $sequence, 'evaluationPeriod' => $this->evaluationPeriod]);
                $markCalculatedFormatted['mark' . $sequence->getId()] = 'NC';
                if ($markSequenceCourseCalculated) {
                    $sequenceMark = $markSequenceCourseCalculated->getMark();
                    $markCalculatedFormatted['mark' . $sequence->getId()] = $sequenceMark;
                }
            }
        }

        $markCalculatedFormatted['mark'] = $markShowed;
        $markCalculatedFormatted['coeff'] = $coeff;
        $markCalculatedFormatted['validatedCredit'] = $validatedCredit;
        $markCalculatedFormatted['consideredCredit'] = $consideredCredit;
        $markCalculatedFormatted['courseStatus'] = $isCourseValidated;
        $markCalculatedFormatted['moduleStatus'] = $isModuleEliminated;

        // Rang des matieres
        if ($this->calculateSubjectsRanks){
            $rank = $markPeriodCourseCalculated->getRank();
            $totalCourseStudentsRegistered = $markPeriodCourseCalculated->getTotalCourseStudentsRegistered();
            $rankShowed = $this->displayNumberOfRows ? $rank.'/'.$totalCourseStudentsRegistered : $rank;
            $markCalculatedFormatted['rank'] = $rankShowed;
        }

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
                $code = $module->getCode();
                $mark = $markPeriodModuleCalculated->getMark();
                $markShowed = $mark;
                if ($mark === null){
                    $markShowed = $this->displayMarksNotEnteredInTheBulletin ? 'X' : '';
                }

                $markCalculatedFormatted = $this->getMarkPeriodModuleCalculatedFormatted($markPeriodModuleCalculated);

                $markCalculatedFormatted['name'] = $name;
                $markCalculatedFormatted['code'] = $code;
                if ($this->displaySequencesInBulletins) {
                    foreach ($this->sequences as $sequence) {
                        $markSequenceModuleCalculated = $this->markSequenceModuleCalculatedRepository->findOneBy(['student' => $student, 'module' => $module, 'sequence' => $sequence, 'evaluationPeriod' => $evaluationPeriod]);
                        $markCalculatedFormatted['mark' . $sequence->getId()] = 'NC';
                        if ($markSequenceModuleCalculated) {
                            $sequenceMark = $markSequenceModuleCalculated->getMark();
                            $markCalculatedFormatted['mark' . $sequence->getId()] = $sequenceMark;
                        }
                    }
                }
                $markCalculatedFormatted['mark'] = $markShowed;

                $markCalculatedFormatted['isModule'] = true;
                $allMarksCalculated[] = $markCalculatedFormatted;

                $markPeriodModuleCalculationRelations = $this->markPeriodModuleCalculationRelationRepository->findBy($criteriaModule);
                $count = count($markPeriodModuleCalculationRelations);
                foreach ($markPeriodModuleCalculationRelations as $i=>$markPeriodModuleCalculationRelation) {
                    $markPeriodCourseCalculated = $markPeriodModuleCalculationRelation->getMarkCourseCalculated();
                    $markCalculatedFormatted = $this->getMarkPeriodCourseCalculatedFormatted($markPeriodCourseCalculated);
                    $markCalculatedFormatted['isFirstCourse'] = $i === 0;
                    $markCalculatedFormatted['courseModuleNumber'] = $count;
                    $allMarksCalculated[] = $markCalculatedFormatted;
                }
            }
        }

        if (!empty($markPeriodCourseCalculateds)) {
            $markCalculatedFormatted = [];
            $markCalculatedFormatted['name'] = 'not module courses';
            $markCalculatedFormatted['code'] = 'not module courses code';
            $markCalculatedFormatted['mark'] = '';
            $markCalculatedFormatted['coeff'] = '';
            $markCalculatedFormatted['description'] = '';
            $markCalculatedFormatted['grade'] = '';
            $markCalculatedFormatted['gpa'] = '';
            $markCalculatedFormatted['averageGpa'] = '';
            $markCalculatedFormatted['isGeneralAverage'] = false;
            $markCalculatedFormatted['isModule'] = true;
            $markCalculatedFormatted['isNotModule'] = true;
            $allMarksCalculated[] = $markCalculatedFormatted;
            $count = count($markPeriodCourseCalculateds);
            foreach ($markPeriodCourseCalculateds as $i => $markPeriodCourseCalculated) {
                $markCalculatedFormatted = $this->getMarkPeriodCourseCalculatedFormatted($markPeriodCourseCalculated);
                $markCalculatedFormatted['isFirstCourse'] = $i === 0;
                $markCalculatedFormatted['courseModuleNumber'] = $count;
                $allMarksCalculated[] = $markCalculatedFormatted;

            }
        }

        $generalAverageResult = [];
        $markCalculatedPositionFormatted = [];
        $positionResult = [];
        if ($markPeriodGeneralAverageCalculated){
            $markCalculatedFormatted = $this->getMarkPeriodModuleCalculatedFormatted($markPeriodGeneralAverageCalculated);
            $markCalculatedPositionFormatted = $markCalculatedFormatted;
            $markCalculatedFormatted['name'] = 'general average';
            $markCalculatedPositionFormatted['name'] = 'position';

            if ($this->displaySequencesInBulletins) {
                foreach ($this->sequences as $sequence) {
                    $markSequenceGeneralAverageCalculated = $this->markSequenceGeneralAverageCalculatedRepository->findOneBy(['student' => $student, 'sequence' => $sequence, 'evaluationPeriod' => $evaluationPeriod]);
                    $markCalculatedFormatted['mark' . $sequence->getId()] = 'NC';
                    $markCalculatedPositionFormatted['mark' . $sequence->getId()] = '';

                    if ($markSequenceGeneralAverageCalculated) {
                        $sequenceAverage = $markSequenceGeneralAverageCalculated->getAverage();
                        $markCalculatedFormatted['mark' . $sequence->getId()] = $sequenceAverage;

                        $isClassed = $markSequenceGeneralAverageCalculated->getIsClassed();
                        $rank = $markSequenceGeneralAverageCalculated->getRank();
                        $totalStudentsClassed = $markSequenceGeneralAverageCalculated->getTotalStudentsClassed();
                        $rankShowed = $isClassed ? ($this->displayNumberOfRows ? $rank . '/' . $totalStudentsClassed : $rank) : 'not classed';

                        $markCalculatedPositionFormatted['mark' . $sequence->getId()] = $rankShowed;

                    }
                }
            }
            $average = $markPeriodGeneralAverageCalculated->getAverage();
            $isClassed = $markPeriodGeneralAverageCalculated->getIsClassed();
            $markShowed = $average; //$average . '/' . 20;

            if ($average === null) {
                $markShowed = 'X';
            }
            $markCalculatedFormatted['mark'] = $markShowed;

            $markCalculatedFormatted['isModule'] = false;
            $markCalculatedFormatted['isGeneralAverage'] = true;
            $markCalculatedFormatted['isEliminated'] = $markPeriodGeneralAverageCalculated->getIsEliminated();


            $rank = $markPeriodGeneralAverageCalculated->getRank();
            $totalStudentsClassed = $markPeriodGeneralAverageCalculated->getTotalStudentsClassed();
            $rankShowed = $isClassed ? ($this->displayNumberOfRows ? $rank . '/' . $totalStudentsClassed : $rank) : 'not classed';

            $markCalculatedFormatted['rank'] = $rankShowed;
            $markCalculatedPositionFormatted['mark'] = $rankShowed;

            $school = $markPeriodGeneralAverageCalculated->getSchool();
            $markGrade = $this->markGradeRepository->findOneBy(['school' => $school], ['gpa' => 'DESC']);

            $averageGpa = $markCalculatedFormatted['averageGpa'];
            if (!isset($averageGpa)){
//                if (isset($markGrade)) $averageGpa .= '/'.$markGrade->getGpa();
                $averageGpa = 'X';
            }

            $markCalculatedFormatted['maxGpa'] = !isset($markGrade) ? '' : ' / '.$markGrade->getGpa();

            $markCalculatedFormatted['averageGpa'] = $averageGpa;
            $generalAverageResult = $markCalculatedFormatted;
            $positionResult = $markCalculatedPositionFormatted;
        }
        $classificationInfos = [];
        $classificationInfos['numberOfAttendedCourses'] = $markPeriodGeneralAverageCalculated->getNumberOfAttendedCourses();
        $classificationInfos['numberOfComposedCourses'] = $markPeriodGeneralAverageCalculated->getNumberOfComposedCourses();
        $classificationInfos['totalOfCreditsAttended'] = $markPeriodGeneralAverageCalculated->getTotalOfCreditsAttended();
        $classificationInfos['totalOfCreditsComposed'] = $markPeriodGeneralAverageCalculated->getTotalOfCreditsComposed();
        $classificationInfos['percentageSubjectNumber'] = $markPeriodGeneralAverageCalculated->getPercentageSubjectNumber();
        $classificationInfos['percentageTotalCoefficient'] = $markPeriodGeneralAverageCalculated->getPercentageTotalCoefficient();
        $classificationInfos['isClassed'] = $isClassed;

        $remarks = ['blameWork'=>'blame work','warningWork'=>'warning work','grantEncouragement'=>'encouragements','grantCongratulation'=>'congratulations'];

        $workRemarks = [];
        foreach ($remarks as $remark=>$remarkLower) {
            $getterMethod = ucfirst($remark);
            if ($markPeriodGeneralAverageCalculated->{'get' . $getterMethod}()) {
                $workRemarks[] = $remarkLower;
            }
        }

        // Informations pour les releves de notes
        $class = $student->getCurrentClass();
        $institution = $student->getInstitution();
        $transcriptInfos = [
            'student'=>$student->getStudent(),
            'evaluationPeriod'=>$evaluationPeriod,
            'class'=> $class,
            'level'=>$class->getLevel(),
            'speciality'=>$class->getSpeciality(),
            'year'=>$student->getCurrentYear(),
            'institution'=> $institution,
            'country'=> $institution->getCountry() ? $institution->getCountry()->getName() : ''
        ];
        $configurations = [
            'displaySequencesInBulletins'=>$this->displaySequencesInBulletins,
            'calculateSubjectsRanks'=>$this->calculateSubjectsRanks,
            'showPhotoOnSummary'=>$this->showPhotoOnSummary,
        ];

        $periodResultInfos = [
            'allMarksCalculated' => $allMarksCalculated,
            'classificationInfos' => $classificationInfos,
            'transcriptInfos' => $transcriptInfos,
            'configurations' => $configurations,
            'generalAverageResult'=> $generalAverageResult,
            'positionResult'=> $positionResult,
            'workRemarks'=>$workRemarks,
            'grantTh'=> $markPeriodGeneralAverageCalculated->getGrantThComposition()
        ];
        if ($this->displaySequencesInBulletins){
            $periodResultInfos['sequences'] = $this->sequences;
        }
        return $periodResultInfos;
    }
}