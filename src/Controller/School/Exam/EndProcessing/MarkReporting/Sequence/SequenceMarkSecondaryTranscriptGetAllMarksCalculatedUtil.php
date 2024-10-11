<?php

namespace App\Controller\School\Exam\EndProcessing\MarkReporting\Sequence;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculated;
use App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculated;
use App\Entity\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculated;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\School\Study\Program\TeacherCourseRegistration;
use App\Repository\School\Exam\Configuration\MarkGradeRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculationRelationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Program\TeacherCourseRegistrationRepository;

class SequenceMarkSecondaryTranscriptGetAllMarksCalculatedUtil
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

    public function getMarkSequenceCourseCalculatedFormatted(MarkSequenceCourseCalculated $markSequenceCourseCalculated): array
    {
        $markCalculatedFormatted = [];

        $studentCourseRegistration = $markSequenceCourseCalculated->getStudentCourseRegistration();
        $classProgram = $markSequenceCourseCalculated->getClassProgram();
        $nameuvc = $classProgram->getNameuvc();
        $codeuvc = $classProgram->getCodeuvc();

        $mark = $markSequenceCourseCalculated->getMark();
        $markShowed = $mark;
        if ($mark === null){
            $markShowed = $this->displayMarksNotEnteredInTheBulletin ? 'X' : '';
        }

        $coeff = $classProgram->getCoeff();

        $total = $markSequenceCourseCalculated->getTotal();
        $isCourseValidated = $markSequenceCourseCalculated->getIsCourseValidated();
        $isClassed = $markSequenceCourseCalculated->getIsClassed();
        $validatedCredit = $consideredCredit = 0;

        if ($isCourseValidated) {
            $validatedCredit = $consideredCredit = $coeff;
        }

        $markGrade = $markSequenceCourseCalculated->getGrade();

        $unvalidatedCourseClassHtml = 'class = \'text-danger fst-italic fw-bold mb-1\'';

        $getCellContent = function (bool $courseStatus,mixed $value) use ($unvalidatedCourseClassHtml): string{
            $markCellContent = '<span '.($courseStatus ? '': $unvalidatedCourseClassHtml).'>';
            $markCellContent .= $value;
            $markCellContent .= '</span><br>';
            return $markCellContent;
        };

        $courseStatus = $isCourseValidated;

        $nameCellContent = $getCellContent($courseStatus, $nameuvc);
        $markCalculatedFormatted['name'] = str_contains($nameCellContent,$unvalidatedCourseClassHtml) ? str_replace($unvalidatedCourseClassHtml,'',$nameCellContent) : $nameCellContent;
        $markCalculatedFormatted['code'] = $codeuvc;


        $markCellContent = '';

        // Construction de la cellule pour la note du trimestre
        $markCellContent = $getCellContent($courseStatus,$markShowed);
        $markCalculatedFormatted['mark'] = $markCellContent;

        //
        $markCalculatedFormatted['coeff'] = $getCellContent($courseStatus,$coeff);
        $markCalculatedFormatted['total'] = $getCellContent($courseStatus,$total);
        $markCalculatedFormatted['validatedCredit'] = $validatedCredit;
        $markCalculatedFormatted['consideredCredit'] = $consideredCredit;
        $markCalculatedFormatted['courseStatus'] = $courseStatus;

        // Rang des matieres
        if ($this->calculateSubjectsRanks){
            $rank = $markSequenceCourseCalculated->getRank();
            $totalCourseStudentsRegistered = $markSequenceCourseCalculated->getTotalCourseStudentsRegistered();
            $rankShowed = isset($rank,$mark) ? ($this->displayNumberOfRowsOnCourses ? $rank.'/'.($this->displayTheClassSizeInRows ? $this->classHeadcount : $totalCourseStudentsRegistered) : $rank)
                : ($isClassed ? '' : 'not classed');

            $markCalculatedFormatted['rank'] = $getCellContent($courseStatus,$rankShowed);
        }

        $markCalculatedFormatted['description'] = $markGrade?->getDescription();
        $markCalculatedFormatted['grade'] = $markGrade?->getCode();
        $markCalculatedFormatted['gpa'] = $markGrade?->getGpa();
        $markCalculatedFormatted['averageGpa'] = $markGrade?->getGpa();
        $markCalculatedFormatted['isModule'] = false;
        $markCalculatedFormatted['isGeneralAverage'] = false;
        $markCalculatedFormatted['isNotModule'] = false;

        // Recuperation de l'enseignant
        $teacherCourseRegistration = $this->teacherCourseRegistrationRepository->findOneBy(['course' => $classProgram, 'type' => 'teacherCm']);
        $teacher = $teacherCourseRegistration?->getTeacher();
        $markCalculatedFormatted['teacher'] = '<br>';
        if ($teacher) {
            $teacherFullName = $teacher->getFirstName() . ' ' . $teacher->getName();
            $markCalculatedFormatted['teacher'] = $getCellContent($courseStatus, $teacherFullName);
        }


        return $markCalculatedFormatted;
    }

    public function getMarkSequenceModuleCalculatedFormatted(MarkSequenceModuleCalculated|MarkSequenceGeneralAverageCalculated $markSequenceModuleCalculated)
    {
        $coeff = $markSequenceModuleCalculated->getTotalCredit();
        $total = $markSequenceModuleCalculated->getTotal();
        $validatedCredit = $markSequenceModuleCalculated->getTotalCreditValidated();
        $consideredCredit = $validatedCredit;

        $markGrade = $markSequenceModuleCalculated->getGrade();

        $averageGpa = $markSequenceModuleCalculated->getAverageGpa();

        $markCalculatedFormatted = [];

        $markCalculatedFormatted['coeff'] = $coeff;
        $markCalculatedFormatted['total'] = $total;
        $markCalculatedFormatted['validatedCredit'] = $validatedCredit;
        $markCalculatedFormatted['consideredCredit'] = $consideredCredit;
//        $markCalculatedFormatted['moduleStatus'] = $markSequenceModuleCalculated->getIsEliminated();
        $markCalculatedFormatted['description'] = $markGrade?->getDescription();
        $markCalculatedFormatted['grade'] = $markGrade?->getCode();
        $markCalculatedFormatted['gpa'] = $markGrade?->getGpa();
        $markCalculatedFormatted['averageGpa'] = $averageGpa;
        $markCalculatedFormatted['isGeneralAverage'] = false;
        $markCalculatedFormatted['isNotModule'] = false;

        return $markCalculatedFormatted;
    }

    function getAllMarksCalculated(StudentRegistration $studentRegistration, EvaluationPeriod $evaluationPeriod,Sequence $sequence)
    {
        $criteria = ['student' => $studentRegistration, 'evaluationPeriod' => $evaluationPeriod,'sequence'=>$sequence];
        $criteriaModule = $criteria;
        $criteriaModule['module'] = null;

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
                $code = $module->getCode();
                $mark = $markSequenceModuleCalculated->getMark();
                $markShowed = $mark;
                if ($mark === null){
                    $markShowed = $this->displayMarksNotEnteredInTheBulletin ? 'X' : '';
                }

                // Ajout de la ligne du module pour l'en tete (Groupe A,B,C etc...)
                $markCalculatedFormatted = $this->getMarkSequenceModuleCalculatedFormatted($markSequenceModuleCalculated);

                $markCalculatedFormatted['name'] = $name;
                $markCalculatedFormatted['code'] = $code;

                $markCalculatedFormatted['mark'] = $markShowed;

                $markCalculatedFormatted['isModule'] = true;
                $markCalculatedFormatted['position'] = $module->getPosition();
                $allMarksCalculated[] = $markCalculatedFormatted;

                $markSequenceModuleCalculationRelations = $this->markSequenceModuleCalculationRelationRepository->findBy($criteriaModule);
                $count = count($markSequenceModuleCalculationRelations);

                $allModuleMarksCalculated = [];
                foreach ($markSequenceModuleCalculationRelations as $i=>$markSequenceModuleCalculationRelation) {
                    $markSequenceCourseCalculated = $markSequenceModuleCalculationRelation->getMarkCourseCalculated();
                    $markCalculatedFormatted = $this->getMarkSequenceCourseCalculatedFormatted($markSequenceCourseCalculated);
                    $markCalculatedFormatted['isLastCourse'] = $i === $count - 1;
                    $markCalculatedFormatted['courseModuleNumber'] = $count;
                    $allModuleMarksCalculated[] = $markCalculatedFormatted;
                }

                $tableHeaders = array_keys($allModuleMarksCalculated[0]);
                $allRowsData = [];
                foreach ($tableHeaders as $header) {
                    $contentCell = '';
                    if (is_int($header)){
                        foreach ($allModuleMarksCalculated as $markCalculatedFormatted) {
                            $headerCellContent = $markCalculatedFormatted[$header]['mark'];
                            $contentCell .= $headerCellContent;
                        }

                        $allRowsData[$header]['mark'] = $contentCell;
                    }
                    else{
                        foreach ($allModuleMarksCalculated as $markCalculatedFormatted) {
                            $headerCellContent = $markCalculatedFormatted[$header];
                            if (!is_bool($headerCellContent)) $contentCell .= $headerCellContent;
                            else $contentCell = $headerCellContent;
                        }

                        $allRowsData[$header] = $contentCell;
                    }
                }

                $allMarksCalculated[] = $allRowsData;

                // Ajout de la ligne du module pour le total
                $markCalculatedFormatted = $this->getMarkSequenceModuleCalculatedFormatted($markSequenceModuleCalculated);
                $markCalculatedFormatted['isModuleTotal'] = true;
                $markCalculatedFormatted['isModule'] = false;
                $markCalculatedFormatted['isNotModule'] = true;
                $markCalculatedFormatted['mark'] = $markSequenceModuleCalculated->getMark();
                $allMarksCalculated[] = $markCalculatedFormatted;

            }
        }

        if (!empty($markSequenceCourseCalculateds)) {
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
            $count = count($markSequenceCourseCalculateds);

            $allModuleMarksCalculated = [];
            foreach ($markSequenceCourseCalculateds as $i => $markSequenceCourseCalculated) {
                $markCalculatedFormatted = $this->getMarkSequenceCourseCalculatedFormatted($markSequenceCourseCalculated);
                $markCalculatedFormatted['isLastCourse'] = $i === $count - 1;
                $markCalculatedFormatted['courseModuleNumber'] = $count;
                $allModuleMarksCalculated[] = $markCalculatedFormatted;
            }

            $tableHeaders = array_keys($allModuleMarksCalculated[0]);
            $allRowsData = [];
            foreach ($tableHeaders as $header) {
                $contentCell = '';
                if (is_int($header)){
                    foreach ($allModuleMarksCalculated as $markCalculatedFormatted) {
                        $headerCellContent = $markCalculatedFormatted[$header]['mark'];
                        $contentCell .= $headerCellContent;
                    }

                    $allRowsData[$header]['mark'] = $contentCell;
                }
                else{
                    foreach ($allModuleMarksCalculated as $markCalculatedFormatted) {
                        $headerCellContent = $markCalculatedFormatted[$header];
                        if (!is_bool($headerCellContent)) $contentCell .= $headerCellContent;
                        else $contentCell = $headerCellContent;
                    }

                    $allRowsData[$header] = $contentCell;
                }
            }

            $allMarksCalculated[] = $allRowsData;
        }

        // Informations des moyennes generales
        $generalAverageResult = [];
        $markCalculatedPositionFormatted = [];
        $positionResult = [];
        $isClassed = false;
        if ($markSequenceGeneralAverageCalculated){
            $markCalculatedFormatted = $this->getMarkSequenceModuleCalculatedFormatted($markSequenceGeneralAverageCalculated);
            $markCalculatedPositionFormatted = $markCalculatedFormatted;
            $markCalculatedFormatted['name'] = 'general average';
            $markCalculatedPositionFormatted['name'] = 'position';

            $average = $markSequenceGeneralAverageCalculated->getAverage();
            $isClassed = $markSequenceGeneralAverageCalculated->getIsClassed();
            $markShowed = $average; //$average . '/' . 20;

            if ($average === null) {
                $markShowed = 'X';
            }
            $markCalculatedFormatted['mark'] = $markShowed;

            $markCalculatedFormatted['isModule'] = false;
            $markCalculatedFormatted['isGeneralAverage'] = true;
            $markCalculatedFormatted['isEliminated'] = $markSequenceGeneralAverageCalculated->getIsEliminated();


            $rank = $markSequenceGeneralAverageCalculated->getRank();
            $totalStudentsClassed = $markSequenceGeneralAverageCalculated->getTotalStudentsClassed();
            $rankShowed = isset($rank,$average) ? ($this->displayNumberOfRowsOnCourses ? $rank.'/'.($this->displayTheClassSizeInRows ? $this->classHeadcount : $totalStudentsClassed) : $rank) : ($isClassed ? '' : 'not classed');

            $markCalculatedFormatted['rank'] = $rankShowed;
            $markCalculatedPositionFormatted['mark'] = $rankShowed;

            $school = $markSequenceGeneralAverageCalculated->getSchool();
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

        // Information sur la classification
        $classificationInfos = [];
        $classificationInfos['numberOfAttendedCourses'] = $markSequenceGeneralAverageCalculated->getNumberOfAttendedCourses();
        $classificationInfos['numberOfComposedCourses'] = $markSequenceGeneralAverageCalculated->getNumberOfComposedCourses();
        $classificationInfos['totalOfCreditsAttended'] = $markSequenceGeneralAverageCalculated->getTotalOfCreditsAttended();
        $classificationInfos['totalOfCreditsComposed'] = $markSequenceGeneralAverageCalculated->getTotalOfCreditsComposed();
        $classificationInfos['percentageSubjectNumber'] = $markSequenceGeneralAverageCalculated->getPercentageSubjectNumber();
        $classificationInfos['percentageTotalCoefficient'] = $markSequenceGeneralAverageCalculated->getPercentageTotalCoefficient();
        $classificationInfos['isClassed'] = $isClassed;

        $remarks = ['blameWork'=>'blame work','warningWork'=>'warning work','grantEncouragement'=>'encouragements','grantCongratulation'=>'congratulations'];

        $workRemarks = [];
        foreach ($remarks as $remark=>$remarkLower) {
            $getterMethod = ucfirst($remark);
            if ($markSequenceGeneralAverageCalculated->{'get' . $getterMethod}()) {
                $workRemarks[] = $remarkLower;
            }
        }
        // Informations pour les releves de notes
        $class = $studentRegistration->getCurrentClass();
        $school = $class->getSchool();

        // Autre informations de la classe
            // Effectif de la classe
            $classHeadcount = $this->studentRegistrationRepository->count(['currentClass'=>$class]);

            // Premier,dernier, moyenne generale
        $markSequenceGeneralAverageCalculatedsClassed = $this->markSequenceGeneralAverageCalculatedRepository->findBy(['class' => $class, 'evaluationPeriod' => $this->evaluationPeriod,'sequence'=>$this->sequence,'isClassed'=>true],['average'=>'DESC']);

//        dd($markSequenceGeneralAverageCalculatedsClassed);
        $firstStudentAverage = $lastStudentAverage = $classAverage = null;

        if (!empty($markSequenceGeneralAverageCalculatedsClassed)) {
            $firstStudentAverage = $markSequenceGeneralAverageCalculatedsClassed[0]->getAverage();
            $totalStudents = count($markSequenceGeneralAverageCalculatedsClassed);
            $lastStudentAverage = $markSequenceGeneralAverageCalculatedsClassed[$totalStudents - 1]->getAverage();
            $totalAverages = 0;
            foreach ($markSequenceGeneralAverageCalculatedsClassed as $markSequenceGeneralAverageCalculatedClassed) {
                $totalAverages += $markSequenceGeneralAverageCalculatedClassed->getAverage();
            }
            $classAverage = round($totalAverages / $totalStudents, 2);
        }

        // Redoublant ou non
        $student = $studentRegistration->getStudent();
//        $oldYearStudentRegistration = $this->studentRegistrationRepository-
        $institution = $studentRegistration->getInstitution();

        // Tableau de la discipline
        $disciplineArray = [];

        $disciplineElements = [
          'justified late arrivals(h)',
          'unjustified late arrivals(h)',
        'justified absences(h)',
          'unjustified absences(h)',
          'warning conduct',
          'warning work',
        'blame conduct',
          'blame work',
        'exclusions(days)'
        ];

        $previousEvaluationPeriods = [...$this->previousEvaluationPeriods,$this->evaluationPeriod];
        foreach ($disciplineElements as $disciplineElement) {
            $disciplineRow = ['name'=>$disciplineElement];
            foreach ($previousEvaluationPeriods as $index=> $previousEvaluationPeriod) {
                $disciplineRow[$previousEvaluationPeriod->getId()] = '';
            }
            $disciplineArray[] = $disciplineRow;
        }

        // Previous general average results
        $previousGeneralAverageResults = [];
        foreach ($this->previousEvaluationPeriods as $previousEvaluationPeriod) {
             $previousGeneralAverageResults[$previousEvaluationPeriod->getId()] = $this->markSequenceGeneralAverageCalculatedRepository->findOneBy(['student'=>$studentRegistration,'evaluationPeriod'=>$previousEvaluationPeriod]);
        }

        foreach ($this->previousSequences as $previousSequence) {
             $previousGeneralAverageResults['seq'.$previousSequence->getId()] = $this->markSequenceGeneralAverageCalculatedRepository->findOneBy(['student'=>$studentRegistration,'evaluationPeriod'=>$evaluationPeriod,'sequence'=>$previousSequence]);
        }

        $transcriptInfos = [
            'student'=>[
              'student'=> $student,
              'previousGeneralAverageResults'=>$previousGeneralAverageResults
            ],

            'evaluationPeriod'=>$evaluationPeriod,
            'sequence'=>$sequence,
            'previousEvaluationPeriods'=> $previousEvaluationPeriods,
            'previousSequences'=> $this->previousSequences,
            'disciplineElements'=>$disciplineArray,

            'class'=> [
                'class'=> $class,
                'classHeadcount'=> $classHeadcount,
                'firstStudentAverage'=>$firstStudentAverage,
                'lastStudentAverage'=>$lastStudentAverage,
                'classAverage'=>$classAverage,
            ],
            'level'=>$class->getLevel(),
            'speciality'=>$class->getSpeciality(),
            'year'=>$studentRegistration->getCurrentYear(),
            'institution'=> $institution,
            'school'=> $school,
            'country'=> $institution->getCountry() ? $institution->getCountry()->getName() : ''
        ];
        $configurations = [
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
        ];
        return $periodResultInfos;
    }
}