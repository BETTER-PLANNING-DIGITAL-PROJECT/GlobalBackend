<?php

namespace App\Controller\School\Exam\EndProcessing\MarkReporting\Period;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculated;
use App\Entity\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculated;
use App\Entity\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculated;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\School\Study\Program\TeacherCourseRegistration;
use App\Repository\School\Exam\Configuration\MarkGradeRepository;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculatedRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Program\TeacherCourseRegistrationRepository;

class PeriodMarkSecondaryTranscriptGetAllMarksCalculatedUtil
{
    public function __construct(
        // Configurations
        private bool $displaySequencesInBulletins,
        private bool $displayNumberOfRows,
        private bool $displayNumberOfRowsOnCourses,
        private bool $displayTheClassSizeInRows,
        private bool $calculateSubjectsRanks,
        private bool $displayMarksNotEnteredInTheBulletin,
        private bool                                                    $showPhotoOnSummary,

        // Attributs principaux
        private int                                                     $classHeadcount,
        private EvaluationPeriod                                        $evaluationPeriod,
        private readonly array                                          $sequences,
        private readonly array                                          $previousEvaluationPeriods,
        private readonly MarkGradeRepository                            $markGradeRepository,
        private readonly MarkSequenceCourseCalculatedRepository         $markSequenceCourseCalculatedRepository,
        private readonly MarkSequenceModuleCalculatedRepository         $markSequenceModuleCalculatedRepository,
        private readonly MarkSequenceGeneralAverageCalculatedRepository $markSequenceGeneralAverageCalculatedRepository,
        private readonly MarkPeriodCourseCalculatedRepository           $markPeriodCourseCalculatedRepository,

        private readonly MarkPeriodModuleCalculatedRepository           $markPeriodModuleCalculatedRepository,
        private readonly MarkPeriodModuleCalculationRelationRepository  $markPeriodModuleCalculationRelationRepository,

        private readonly MarkPeriodGeneralAverageCalculatedRepository   $markPeriodGeneralAverageCalculatedRepository,
        private readonly StudentRegistrationRepository                  $studentRegistrationRepository,
        private readonly TeacherCourseRegistrationRepository            $teacherCourseRegistrationRepository
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

        $total = $markPeriodCourseCalculated->getTotal();
        $isCourseValidated = $markPeriodCourseCalculated->getIsCourseValidated();
        $validatedCredit = $consideredCredit = 0;
        $isModuleEliminated = $markPeriodCourseCalculated->getIsModuleEliminated();
        if ($isCourseValidated !== 'nv') {
            if ($isCourseValidated === 'v') $validatedCredit = $coeff;
            if (!$isModuleEliminated) $consideredCredit = $coeff;
        }

        $markGrade = $markPeriodCourseCalculated->getGrade();

        $unvalidatedCourseClassHtml = 'class = \'text-danger fst-italic fw-bold mb-1\'';

        $getCellContent = function (bool $courseStatus,mixed $value) use ($unvalidatedCourseClassHtml): string{
            $markCellContent = '<span '.($courseStatus ? '': $unvalidatedCourseClassHtml).'>';
            $markCellContent .= $value;
            $markCellContent .= '</span><br>';
            return $markCellContent;
        };

        $courseStatus = str_contains('mv',$isCourseValidated);

        $nameCellContent = $getCellContent($courseStatus, $nameuvc);

        // Permet de retirer la couleur rouge du text-danger sur le nom de la matiere meme si elle n'est pas validee
        // Car dans le bulletin , le nom de la matiere est toujours sans style meme si elle est non validee
        $markCalculatedFormatted['name'] = str_contains($nameCellContent,$unvalidatedCourseClassHtml) ? str_replace($unvalidatedCourseClassHtml,'',$nameCellContent) : $nameCellContent;
        $markCalculatedFormatted['code'] = $codeuvc;


        $markCellContent = '';
        if ($this->displaySequencesInBulletins) {
            foreach ($this->sequences as $sequence) {
                $markSequenceCourseCalculated = $this->markSequenceCourseCalculatedRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $sequence, 'evaluationPeriod' => $this->evaluationPeriod]);
                $sequenceMark = 'NC';
                $sequenceCourseStatus = false;
                $markCalculatedFormatted[$sequence->getId()] = [
                    'mark'=>$sequenceMark,
                    'courseStatus'=> $sequenceCourseStatus,
                ];
                if ($markSequenceCourseCalculated) {
                    $sequenceMark = $markSequenceCourseCalculated->getMark();
                    $markCalculatedFormatted[$sequence->getId()]['mark'] = $sequenceMark;
                    $isCourseSequenceValidated = $markSequenceCourseCalculated->getIsCourseValidated();
                    $sequenceCourseStatus = $markCalculatedFormatted[$sequence->getId()]['courseStatus'] = $isCourseSequenceValidated;
                }

                $markCellContent = $getCellContent($sequenceCourseStatus,$sequenceMark);
                $markCalculatedFormatted[$sequence->getId()]['mark'] = $markCellContent;
            }
        }

        // Construction de la cellule pour la note du trimestre
        $markCellContent = $getCellContent($courseStatus,$markShowed);
        $markCalculatedFormatted['mark'] = $markCellContent;

        //
        $markCalculatedFormatted['coeff'] = $getCellContent($courseStatus,$coeff);
        $markCalculatedFormatted['total'] = $getCellContent($courseStatus,$total);
        $markCalculatedFormatted['validatedCredit'] = $validatedCredit;
        $markCalculatedFormatted['consideredCredit'] = $consideredCredit;
        $markCalculatedFormatted['courseStatus'] = $courseStatus;
        $markCalculatedFormatted['moduleStatus'] = $isModuleEliminated;

        $isClassed = $markPeriodCourseCalculated->getIsClassed();
        // Rang des matieres
        if ($this->calculateSubjectsRanks){
            $rank = $markPeriodCourseCalculated->getRank();
            $totalCourseStudentsRegistered = $markPeriodCourseCalculated->getTotalCourseStudentsRegistered();
            $rankShowed = isset($rank,$mark) ? ($this->displayNumberOfRows ? $rank.'/'.($this->displayTheClassSizeInRows ? $this->classHeadcount : $totalCourseStudentsRegistered) : $rank)
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

    public function getMarkPeriodModuleCalculatedFormatted(MarkPeriodModuleCalculated|MarkPeriodGeneralAverageCalculated $markPeriodModuleCalculated)
    {
        $coeff = $markPeriodModuleCalculated->getTotalCredit();
        $total = $markPeriodModuleCalculated->getTotal();
        $validatedCredit = $markPeriodModuleCalculated->getTotalCreditValidated();
        $consideredCredit = $markPeriodModuleCalculated->getTotalCreditConsidered();

        $markGrade = $markPeriodModuleCalculated->getGrade();

        $averageGpa = $markPeriodModuleCalculated->getAverageGpa();

        $markCalculatedFormatted = [];

        $markCalculatedFormatted['coeff'] = $coeff;
        $markCalculatedFormatted['total'] = $total;
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

    function getAllMarksCalculated(StudentRegistration $studentRegistration, EvaluationPeriod $evaluationPeriod)
    {
        $criteria = ['student' => $studentRegistration, 'evaluationPeriod' => $evaluationPeriod];
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

                // Ajout de la ligne du module pour l'en tete (Groupe A,B,C etc...)
                $markCalculatedFormatted = $this->getMarkPeriodModuleCalculatedFormatted($markPeriodModuleCalculated);

                $markCalculatedFormatted['name'] = $name;
                $markCalculatedFormatted['code'] = $code;
                if ($this->displaySequencesInBulletins) {
                    foreach ($this->sequences as $sequence) {
                        $markSequenceModuleCalculated = $this->markSequenceModuleCalculatedRepository->findOneBy(['student' => $studentRegistration, 'module' => $module, 'sequence' => $sequence, 'evaluationPeriod' => $evaluationPeriod]);
                        $markCalculatedFormatted['mark' . $sequence->getId()] = 'NC';
                        if ($markSequenceModuleCalculated) {
                            $sequenceMark = $markSequenceModuleCalculated->getMark();
                            $markCalculatedFormatted['mark' . $sequence->getId()] = $sequenceMark;
                        }
                    }
                }
                $markCalculatedFormatted['mark'] = $markShowed;

                $markCalculatedFormatted['isModule'] = true;
                $markCalculatedFormatted['position'] = $module->getPosition();
                $allMarksCalculated[] = $markCalculatedFormatted;

                // Fin

                $markPeriodModuleCalculationRelations = $this->markPeriodModuleCalculationRelationRepository->findBy($criteriaModule);
                $count = count($markPeriodModuleCalculationRelations);

                $allModuleMarksCalculated = [];
                foreach ($markPeriodModuleCalculationRelations as $i=>$markPeriodModuleCalculationRelation) {
                    $markPeriodCourseCalculated = $markPeriodModuleCalculationRelation->getMarkCourseCalculated();
                    $markCalculatedFormatted = $this->getMarkPeriodCourseCalculatedFormatted($markPeriodCourseCalculated);
                    $markCalculatedFormatted['isLastCourse'] = $i === $count - 1;
                    $markCalculatedFormatted['courseModuleNumber'] = $count;
                    $allModuleMarksCalculated[] = $markCalculatedFormatted;
                }

                // Partie pour fusionner les cellules pour chaque info du tableau (nom matiere,code,note etc...) (les tableHeaders)
                // On melange les cellules ici car au front-end en modifiant les fichiers de style avec border-bottom : 0px ou
                // border-bottom-color : white ca ne passait pas sur l'affichage
                $tableHeaders = array_keys($allModuleMarksCalculated[0]);
                $allRowsData = [];
                foreach ($tableHeaders as $header) {
                    $contentCell = '';
                    // Si c'est une note de sequence ou periode ep3 , ep3seq1
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
                            // Mettre le contenu de la cellule en fonction de la valeur de la variable pour les autres infos
                            // On fusionne les informations des cellules telles que le nom,note
                            if (!is_bool($headerCellContent)) $contentCell .= $headerCellContent;

                            // Ceux qui sont les booleens (isGeneralAverage,isModule), on renvoit telle qu'elle est
                            else $contentCell = $headerCellContent;
                        }

                        $allRowsData[$header] = $contentCell;
                    }
                }

                $allMarksCalculated[] = $allRowsData;

                // Ajout de la ligne du module pour le total
                $markCalculatedFormatted = $this->getMarkPeriodModuleCalculatedFormatted($markPeriodModuleCalculated);
                $markCalculatedFormatted['isModuleTotal'] = true;
                $markCalculatedFormatted['isModule'] = false;
                $markCalculatedFormatted['isNotModule'] = true;
                $markCalculatedFormatted['mark'] = $markPeriodModuleCalculated->getMark();
                $allMarksCalculated[] = $markCalculatedFormatted;

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

            $allModuleMarksCalculated = [];
            foreach ($markPeriodCourseCalculateds as $i => $markPeriodCourseCalculated) {
                $markCalculatedFormatted = $this->getMarkPeriodCourseCalculatedFormatted($markPeriodCourseCalculated);
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
        if ($markPeriodGeneralAverageCalculated){
            $markCalculatedFormatted = $this->getMarkPeriodModuleCalculatedFormatted($markPeriodGeneralAverageCalculated);
            $markCalculatedPositionFormatted = $markCalculatedFormatted;
            $markCalculatedFormatted['name'] = 'general average';
            $markCalculatedPositionFormatted['name'] = 'position';

            if ($this->displaySequencesInBulletins) {
                foreach ($this->sequences as $sequence) {
                    $markSequenceGeneralAverageCalculated = $this->markSequenceGeneralAverageCalculatedRepository->findOneBy(['student' => $studentRegistration, 'sequence' => $sequence, 'evaluationPeriod' => $evaluationPeriod]);
                    $markCalculatedFormatted['mark' . $sequence->getId()] = 'NC';
                    $markCalculatedPositionFormatted['mark' . $sequence->getId()] = '';

                    if ($markSequenceGeneralAverageCalculated) {
                        $sequenceAverage = $markSequenceGeneralAverageCalculated->getAverage();
                        $markCalculatedFormatted['mark' . $sequence->getId()] = $sequenceAverage;

                        $isClassed = $markSequenceGeneralAverageCalculated->getIsClassed();
                        $rank = $markSequenceGeneralAverageCalculated->getRank();
                        $totalStudentsClassed = $markSequenceGeneralAverageCalculated->getTotalStudentsClassed();
                        $rankShowed = isset($rank,$sequenceAverage) ? ($this->displayNumberOfRows ? $rank.'/'.($this->displayTheClassSizeInRows ? $this->classHeadcount : $totalStudentsClassed) : $rank) : ($isClassed ? '' : 'not classed');

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
            $rankShowed = isset($rank,$average) ? ($this->displayNumberOfRows ? $rank.'/'.($this->displayTheClassSizeInRows ? $this->classHeadcount : $totalStudentsClassed) : $rank) : ($isClassed ? '' : 'not classed');

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

        // Information sur la classification
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
        $class = $studentRegistration->getCurrentClass();
        $school = $class->getSchool();

        // Autre informations de la classe
            // Effectif de la classe
            $classHeadcount = $this->studentRegistrationRepository->count(['currentClass'=>$class]);

            // Premier,dernier, moyenne generale
        $markPeriodGeneralAverageCalculatedsClassed = $this->markPeriodGeneralAverageCalculatedRepository->findBy(['class' => $class, 'evaluationPeriod' => $this->evaluationPeriod,'isClassed'=>true],['average'=>'DESC']);

//        dd($markPeriodGeneralAverageCalculatedsClassed);
        $firstStudentAverage = $lastStudentAverage = $classAverage = null;

        if (!empty($markPeriodGeneralAverageCalculatedsClassed)) {
            $firstStudentAverage = $markPeriodGeneralAverageCalculatedsClassed[0]->getAverage();
            $totalStudents = count($markPeriodGeneralAverageCalculatedsClassed);
            $lastStudentAverage = $markPeriodGeneralAverageCalculatedsClassed[$totalStudents - 1]->getAverage();
            $totalAverages = 0;
            foreach ($markPeriodGeneralAverageCalculatedsClassed as $markPeriodGeneralAverageCalculatedClassed) {
                $totalAverages += $markPeriodGeneralAverageCalculatedClassed->getAverage();
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
             $previousGeneralAverageResults[$previousEvaluationPeriod->getId()] = $this->markPeriodGeneralAverageCalculatedRepository->findOneBy(['student'=>$studentRegistration,'evaluationPeriod'=>$previousEvaluationPeriod]);
        }

        $transcriptInfos = [
            'student'=>[
              'student'=> $student,
              'previousGeneralAverageResults'=>$previousGeneralAverageResults
            ],

            'evaluationPeriod'=>$evaluationPeriod,
            'previousEvaluationPeriods'=> $previousEvaluationPeriods,
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