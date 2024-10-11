<?php

namespace App\Controller\School\Exam\EndProcessing\MarkReporting\Annual;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Annual\Course\MarkAnnualCourseCalculated;
use App\Entity\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCalculated;
use App\Entity\School\Exam\Operation\Annual\Module\MarkAnnualModuleCalculated;
use App\Entity\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculated;
use App\Entity\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculated;
use App\Entity\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculated;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\MarkGradeRepository;
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
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Program\TeacherCourseRegistrationRepository;

class AnnualMarkSecondaryTranscriptGetAllMarksCalculatedUtil
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
        private StudentRegistration $student,
        private array                                                   $evaluationPeriods,
        private array                                                   $sequences,
        private array                                                   $evaluationPeriodsArray,
        private readonly EvaluationPeriodRepository                     $evaluationPeriodRepository,
        private readonly StudentCourseRegistrationRepository            $studentCourseRegistrationRepository,
        private readonly MarkGradeRepository                            $markGradeRepository,
        private readonly MarkSequenceCourseCalculatedRepository         $markSequenceCourseCalculatedRepository,
        private readonly MarkSequenceModuleCalculatedRepository         $markSequenceModuleCalculatedRepository,
        private readonly MarkSequenceGeneralAverageCalculatedRepository $markSequenceGeneralAverageCalculatedRepository,
        private readonly MarkPeriodCourseCalculatedRepository           $markPeriodCourseCalculatedRepository,

        private readonly MarkPeriodModuleCalculatedRepository           $markPeriodModuleCalculatedRepository,
        private readonly MarkPeriodModuleCalculationRelationRepository  $markPeriodModuleCalculationRelationRepository,

        private readonly MarkPeriodGeneralAverageCalculatedRepository   $markPeriodGeneralAverageCalculatedRepository,
        private readonly MarkAnnualCourseCalculatedRepository   $markAnnualCourseCalculatedRepository,
        private readonly MarkAnnualModuleCalculatedRepository   $markAnnualModuleCalculatedRepository,
        private readonly MarkAnnualModuleCalculationRelationRepository   $markAnnualModuleCalculationRelationRepository,
        private readonly MarkAnnualGeneralAverageCalculatedRepository   $markAnnualGeneralAverageCalculatedRepository,
        private readonly StudentRegistrationRepository                  $studentRegistrationRepository,
        private readonly TeacherCourseRegistrationRepository            $teacherCourseRegistrationRepository
    )
    {
    }

    public function getMarkAnnualCourseCalculatedFormatted(MarkAnnualCourseCalculated $markAnnualCourseCalculated): array
    {
        $markCalculatedFormatted = [];

        $classProgram = $markAnnualCourseCalculated->getClassProgram();
        $nameuvc = $classProgram->getNameuvc();
        $codeuvc = $classProgram->getCodeuvc();

        $mark = $markAnnualCourseCalculated->getMark();
        $markShowed = $mark;
        if ($mark === null){
            $markShowed = $this->displayMarksNotEnteredInTheBulletin ? 'X' : '';
        }

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

        if ($this->displaySequencesInBulletins) {
            // On ajoute les notes des sequences et des periodes
            foreach ($this->evaluationPeriodsArray as $evaluationPeriodId => $sequences) {
                $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);
                $studentCourseRegistration = $this->studentCourseRegistrationRepository->findByStudentAndClassProgram($this->student, $classProgram, $evaluationPeriod);

                foreach ($sequences as $sequence) {
                    $markSequenceCourseCalculated = $this->markSequenceCourseCalculatedRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $sequence, 'evaluationPeriod' => $evaluationPeriod]);
                    $sequenceMark = 'NC';
                    $sequenceCourseStatus = false;
                    $markCalculatedFormatted['ep'.$evaluationPeriodId.'seq'.$sequence->getId()] = [
                        'mark'=>$sequenceMark,
                        'courseStatus'=> $sequenceCourseStatus,
                    ];
                    if ($markSequenceCourseCalculated) {
                        $sequenceMark = $markSequenceCourseCalculated->getMark();
                        $markCalculatedFormatted['ep'.$evaluationPeriodId.'seq'.$sequence->getId()]['mark'] = $sequenceMark;
                        $isCourseSequenceValidated = $markSequenceCourseCalculated->getIsCourseValidated();
                        $sequenceCourseStatus = $markCalculatedFormatted['ep'.$evaluationPeriodId.'seq'.$sequence->getId()]['courseStatus'] = $isCourseSequenceValidated;
                    }

                    $markCellContent = $getCellContent($sequenceCourseStatus,$sequenceMark);
                    $markCalculatedFormatted['ep'.$evaluationPeriodId.'seq'.$sequence->getId()]['mark'] = $markCellContent;
                }

                // Construction de la cellule pour la note du trimestre
                $markPeriodCourseCalculated = $this->markPeriodCourseCalculatedRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration, 'evaluationPeriod' => $evaluationPeriod]);
                $periodMark = 'NC';
                $periodCourseStatus = false;
                $markCalculatedFormatted['ep'.$evaluationPeriodId] = [
                    'mark'=>$periodMark,
                    'courseStatus'=> $periodCourseStatus,
                ];
                if ($markPeriodCourseCalculated) {
                    $periodMark = $markPeriodCourseCalculated->getMark();
                    $markCalculatedFormatted['ep'.$evaluationPeriodId]['mark'] = $periodMark;
                    $isCoursePeriodValidated = str_contains('mv',$markPeriodCourseCalculated->getIsCourseValidated());
                    $periodCourseStatus = $markCalculatedFormatted['ep'.$evaluationPeriodId]['courseStatus'] = $isCoursePeriodValidated;
                }

                $markCellContent = $getCellContent($periodCourseStatus,$periodMark);
                $markCalculatedFormatted['ep'.$evaluationPeriodId]['mark'] = $markCellContent;
            }
        } else {
            // On ajoute les notes des periodes
            foreach ($this->evaluationPeriods as $evaluationPeriod) {
                // Construction de la cellule pour la note du trimestre
                $evaluationPeriodId = $evaluationPeriod->getId();
                $studentCourseRegistration = $this->studentCourseRegistrationRepository->findByStudentAndClassProgram($this->student, $classProgram, $evaluationPeriod);
                $markPeriodCourseCalculated = $this->markPeriodCourseCalculatedRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration, 'evaluationPeriod' => $evaluationPeriod]);
                $periodMark = 'NC';
                $periodCourseStatus = false;
                $markCalculatedFormatted['ep'.$evaluationPeriodId] = [
                    'mark'=>$periodMark,
                    'courseStatus'=> $periodCourseStatus,
                ];
                if ($markPeriodCourseCalculated) {
                    $periodMark = $markPeriodCourseCalculated->getMark();
                    $markCalculatedFormatted['ep'.$evaluationPeriodId]['mark'] = $periodMark;
                    $isCoursePeriodValidated = str_contains('mv',$markPeriodCourseCalculated->getIsCourseValidated());
                    $periodCourseStatus = $markCalculatedFormatted['ep'.$evaluationPeriodId]['courseStatus'] = $isCoursePeriodValidated;
                }

                $markCellContent = $getCellContent($periodCourseStatus,$periodMark);
                $markCalculatedFormatted['ep'.$evaluationPeriodId]['mark'] = $markCellContent;
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
        $markCalculatedFormatted['courseStatus'] = $isCourseValidated;
        $markCalculatedFormatted['moduleStatus'] = $isModuleEliminated;

        $isClassed = $markAnnualCourseCalculated->getIsClassed();
        // Rang des matieres
        if ($this->calculateSubjectsRanks){
            $rank = $markAnnualCourseCalculated->getRank();
            $totalCourseStudentsRegistered = $markAnnualCourseCalculated->getTotalCourseStudentsRegistered();
            $rankShowed = isset($rank,$mark) ? ($this->displayNumberOfRowsOnCourses ? $rank.'/'.($this->displayTheClassSizeInRows ? $this->classHeadcount : $totalCourseStudentsRegistered) : $rank) : ($isClassed ? '' : 'not classed');
            $markCalculatedFormatted['rank'] = $getCellContent($courseStatus, $rankShowed);
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

    public function getMarkAnnualModuleCalculatedFormatted(MarkAnnualModuleCalculated|MarkAnnualGeneralAverageCalculated $markAnnualModuleCalculated)
    {
        $total = $markAnnualModuleCalculated->getTotal();
        $coeff = $markAnnualModuleCalculated->getTotalCredit();
        $validatedCredit = $markAnnualModuleCalculated->getTotalCreditValidated();
        $consideredCredit = $markAnnualModuleCalculated->getTotalCreditConsidered();

        $markGrade = $markAnnualModuleCalculated->getGrade();

        $averageGpa = $markAnnualModuleCalculated->getAverageGpa();

        $markCalculatedFormatted = [];

        $markCalculatedFormatted['total'] = $total;
        $markCalculatedFormatted['coeff'] = $coeff;
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

    function getAllAnnualMarksCalculated(StudentRegistration $studentRegistration){
        $criteria = ['student' => $studentRegistration];
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
                $code = $module->getCode();
                $mark = $markAnnualModuleCalculated->getMark();
                $markShowed = $mark;
                if ($mark === null){
                    $markShowed = $this->displayMarksNotEnteredInTheBulletin ? 'X' : '';
                }

                // Ajout de la ligne du module pour l'en tete (Groupe A,B,C etc...)
                $markCalculatedFormatted = $this->getMarkAnnualModuleCalculatedFormatted($markAnnualModuleCalculated);

                $markCalculatedFormatted['name'] = $name;
                $markCalculatedFormatted['code'] = $code;
                if ($this->displaySequencesInBulletins) {
                    // On ajoute les notes des sequences et des periodes
                    foreach ($this->evaluationPeriodsArray as $evaluationPeriodId => $sequences) {
                        $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);

                        foreach ($sequences as $sequence) {
                            $markSequenceModuleCalculated = $this->markSequenceModuleCalculatedRepository->findOneBy(['student'=>$this->student,'module' => $module, 'sequence' => $sequence, 'evaluationPeriod' => $evaluationPeriod]);
                            $sequenceMark = 'NC';
                            $sequenceModuleStatus = false;
                            $markCalculatedFormatted['ep'.$evaluationPeriodId.'seq'.$sequence->getId()] = [
                                'mark'=>$sequenceMark,
                                'courseStatus'=> $sequenceModuleStatus,
                            ];
                            if ($markSequenceModuleCalculated) {
                                $sequenceMark = $markSequenceModuleCalculated->getMark();
                                $markCalculatedFormatted['ep'.$evaluationPeriodId.'seq'.$sequence->getId()]['mark'] = $sequenceMark;
                                $isModuleSequenceValidated = true;//$markSequenceModuleCalculated->getIsModuleValidated();
                                $sequenceModuleStatus = $markCalculatedFormatted['ep'.$evaluationPeriodId.'seq'.$sequence->getId()]['courseStatus'] = $isModuleSequenceValidated;
                            }

                            $markCellContent = $sequenceMark;// Les notes des modules s'affiche de la meme maniere validee ou non $getCellContent($sequenceModuleStatus,$sequenceMark);
                            $markCalculatedFormatted['ep'.$evaluationPeriodId.'seq'.$sequence->getId()]['mark'] = $markCellContent;
                        }

                        // Construction de la cellule pour la note du trimestre
                        $markPeriodModuleCalculated = $this->markPeriodModuleCalculatedRepository->findOneBy(['student' => $this->student,'module'=>$module, 'evaluationPeriod' => $evaluationPeriod]);
                        $periodMark = 'NC';
                        $periodModuleStatus = false;
                        $markCalculatedFormatted['ep'.$evaluationPeriodId] = [
                            'mark'=>$periodMark,
                            'courseStatus'=> $periodModuleStatus,
                        ];
                        if ($markPeriodModuleCalculated) {
                            $periodMark = $markPeriodModuleCalculated->getMark();
                            $markCalculatedFormatted['ep'.$evaluationPeriodId]['mark'] = $periodMark;
                            $isModulePeriodValidated = true; //str_contains('mv',$markPeriodModuleCalculated->getIsModuleValidated());
                            $periodModuleStatus = $markCalculatedFormatted['ep'.$evaluationPeriodId]['courseStatus'] = $isModulePeriodValidated;
                        }

                        $markCellContent = $periodMark;//Les notes des modules s'affiche de la meme maniere validee ou non $getCellContent($periodModuleStatus,$periodMark);
                        $markCalculatedFormatted['ep'.$evaluationPeriodId]['mark'] = $markCellContent;
                    }
                } else {
                    // On ajoute les notes des periodes
                    foreach ($this->evaluationPeriods as $evaluationPeriod) {
                        // Construction de la cellule pour la note du trimestre
                        $evaluationPeriodId = $evaluationPeriod->getId();
                        $markPeriodModuleCalculated = $this->markPeriodModuleCalculatedRepository->findOneBy(['student' => $studentRegistration,'module'=>$module, 'evaluationPeriod' => $evaluationPeriod]);
                        $periodMark = 'NC';
                        $periodModuleStatus = false;
                        $markCalculatedFormatted['ep'.$evaluationPeriodId] = [
                            'mark'=>$periodMark,
                            'courseStatus'=> $periodModuleStatus,
                        ];
                        if ($markPeriodModuleCalculated) {
                            $periodMark = $markPeriodModuleCalculated->getMark();
                            $markCalculatedFormatted['ep'.$evaluationPeriodId]['mark'] = $periodMark;
                            $isModulePeriodValidated = $periodMark; //str_contains('mv',$markPeriodModuleCalculated->getIsModuleValidated());
                            $periodModuleStatus = $markCalculatedFormatted['ep'.$evaluationPeriodId]['courseStatus'] = $isModulePeriodValidated;
                        }

                        $markCellContent = $periodMark;//$getCellContent($periodModuleStatus,$periodMark);
                        $markCalculatedFormatted['ep'.$evaluationPeriodId]['mark'] = $markCellContent;
                    }
                }

                $markCalculatedFormatted['mark'] = $markShowed;

                $markCalculatedFormatted['isModule'] = true;
                $markCalculatedFormatted['position'] = $module->getPosition();
                $allMarksCalculated[] = $markCalculatedFormatted;

                // Fin

                $markAnnualModuleCalculationRelations = $this->markAnnualModuleCalculationRelationRepository->findBy($criteriaModule);
                $count = count($markAnnualModuleCalculationRelations);

                $allModuleMarksCalculated = [];
                foreach ($markAnnualModuleCalculationRelations as $i=>$markAnnualModuleCalculationRelation) {
                    $markAnnualCourseCalculated = $markAnnualModuleCalculationRelation->getMarkCourseCalculated();
                    $markCalculatedFormatted = $this->getMarkAnnualCourseCalculatedFormatted($markAnnualCourseCalculated);
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
                    if (str_starts_with($header,'ep')){
                        foreach ($allModuleMarksCalculated as $markCalculatedFormatted) {
                            $headerCellContent = $markCalculatedFormatted[$header]['mark'];
                            $contentCell .= $headerCellContent;
                        }

                        $allRowsData[$header]['mark'] = $contentCell;
                    }
                    else{
//                        dd($allModuleMarksCalculated);
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
                $markCalculatedFormatted = $this->getMarkAnnualModuleCalculatedFormatted($markAnnualModuleCalculated);
                $markCalculatedFormatted['isModuleTotal'] = true;
                $markCalculatedFormatted['isModule'] = false;
                $markCalculatedFormatted['isNotModule'] = true;
                $markCalculatedFormatted['mark'] = $markAnnualModuleCalculated->getMark();
                $allMarksCalculated[] = $markCalculatedFormatted;

            }
        }

        if (!empty($markAnnualCourseCalculateds)) {
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
            $count = count($markAnnualCourseCalculateds);

            $allModuleMarksCalculated = [];
            foreach ($markAnnualCourseCalculateds as $i => $markAnnualCourseCalculated) {
                $markCalculatedFormatted = $this->getMarkAnnualCourseCalculatedFormatted($markAnnualCourseCalculated);
                $markCalculatedFormatted['isLastCourse'] = $i === $count - 1;
                $markCalculatedFormatted['courseModuleNumber'] = $count;
                $allModuleMarksCalculated[] = $markCalculatedFormatted;
            }

            $tableHeaders = array_keys($allModuleMarksCalculated[0]);
            $allRowsData = [];
            foreach ($tableHeaders as $header) {
                $contentCell = '';
                // Les explications sont au dessus
                // Ouaih les consequences du copier coller
                if (str_starts_with($header,'ep')){
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

        return $allMarksCalculated;
    }

    function getAllMarksCalculated(StudentRegistration $studentRegistration)
    {

        $allMarksCalculated = $this->getAllAnnualMarksCalculated($studentRegistration);

        // Informations des moyennes generales
        $generalAverageResult = [];
        $markCalculatedPositionFormatted = [];
        $positionResult = [];
        $isClassed = false;

        $markAnnualGeneralAverageCalculated = $this->markAnnualGeneralAverageCalculatedRepository->findOneBy(['student' => $studentRegistration]);
        if ($markAnnualGeneralAverageCalculated){
            $markCalculatedFormatted = $this->getMarkAnnualModuleCalculatedFormatted($markAnnualGeneralAverageCalculated);
            $markCalculatedPositionFormatted = $markCalculatedFormatted;
            $markCalculatedFormatted['name'] = 'general average';
            $markCalculatedPositionFormatted['name'] = 'position';

            // Ici , c'est pour les releves primaires
            if ($this->displaySequencesInBulletins) {
                foreach ($this->sequences as $sequence) {
                    $markSequenceGeneralAverageCalculated = $this->markSequenceGeneralAverageCalculatedRepository->findOneBy(['student' => $studentRegistration, 'sequence' => $sequence]);
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
            $average = $markAnnualGeneralAverageCalculated->getAverage();
            $isClassed = $markAnnualGeneralAverageCalculated->getIsClassed();
            $markShowed = $average; //$average . '/' . 20;

            if ($average === null) {
                $markShowed = 'X';
            }
            $markCalculatedFormatted['mark'] = $markShowed;

            $markCalculatedFormatted['isModule'] = false;
            $markCalculatedFormatted['isGeneralAverage'] = true;
            $markCalculatedFormatted['isEliminated'] = $markAnnualGeneralAverageCalculated->getIsEliminated();


            $rank = $markAnnualGeneralAverageCalculated->getRank();
            $totalStudentsClassed = $markAnnualGeneralAverageCalculated->getTotalStudentsClassed();

            //($this->displayNumberOfRows ? $rank.'/'.$totalStudentsClassed : $rank)
            // On affiche l'effectif en haut pour le secondaire
            $rankShowed = isset($rank,$average) ? $rank : ($isClassed ? '' : 'not classed');

            $markCalculatedFormatted['rank'] = $rankShowed;
            $markCalculatedPositionFormatted['mark'] = $rankShowed;

            $school = $markAnnualGeneralAverageCalculated->getSchool();
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
        $classificationInfos['numberOfAttendedCourses'] = $markAnnualGeneralAverageCalculated->getNumberOfAttendedCourses();
        $classificationInfos['numberOfComposedCourses'] = $markAnnualGeneralAverageCalculated->getNumberOfComposedCourses();
        $classificationInfos['totalOfCreditsAttended'] = $markAnnualGeneralAverageCalculated->getTotalOfCreditsAttended();
        $classificationInfos['totalOfCreditsComposed'] = $markAnnualGeneralAverageCalculated->getTotalOfCreditsComposed();
        $classificationInfos['percentageSubjectNumber'] = $markAnnualGeneralAverageCalculated->getPercentageSubjectNumber();
        $classificationInfos['percentageTotalCoefficient'] = $markAnnualGeneralAverageCalculated->getPercentageTotalCoefficient();
        $classificationInfos['isClassed'] = $isClassed;

        $remarks = ['blameWork'=>'blame work','warningWork'=>'warning work','grantEncouragement'=>'encouragements','grantCongratulation'=>'congratulations'];

        $workRemarks = [];
        foreach ($remarks as $remark=>$remarkLower) {
            $getterMethod = ucfirst($remark);
            if ($markAnnualGeneralAverageCalculated->{'get' . $getterMethod}()) {
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
        $markAnnualGeneralAverageCalculatedsClassed = $this->markAnnualGeneralAverageCalculatedRepository->findBy(['class' => $class,'isClassed'=>true],['average'=>'DESC']);

        //        dd($markAnnualGeneralAverageCalculatedsClassed);
        $firstStudentAverage = $lastStudentAverage = $classAverage = null;

        if (!empty($markAnnualGeneralAverageCalculatedsClassed)) {
            $firstStudentAverage = $markAnnualGeneralAverageCalculatedsClassed[0]->getAverage();
            $totalStudents = count($markAnnualGeneralAverageCalculatedsClassed);
            $lastStudentAverage = $markAnnualGeneralAverageCalculatedsClassed[$totalStudents - 1]->getAverage();
            $totalAverages = 0;
            foreach ($markAnnualGeneralAverageCalculatedsClassed as $markAnnualGeneralAverageCalculatedClassed) {
                $totalAverages += $markAnnualGeneralAverageCalculatedClassed->getAverage();
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

        foreach ($disciplineElements as $disciplineElement) {
            $disciplineRow = ['name'=>$disciplineElement];
            foreach ($this->evaluationPeriods as $evaluationPeriod) {
                $disciplineRow['ep'.$evaluationPeriod->getId()] = '';
            }
            $disciplineRow['an'] = '';
            $disciplineArray[] = $disciplineRow;
        }

        // Previous general average results
        $previousGeneralAverageResults = [];
        foreach ($this->evaluationPeriodsArray as $evaluationPeriodId => $sequences) {
            $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);
            foreach ($sequences as $sequence) {
                $previousGeneralAverageResults['ep'.$evaluationPeriod->getId().'seq'.$sequence->getId()] = $this->markSequenceGeneralAverageCalculatedRepository->findOneBy(['student'=>$studentRegistration,'sequence'=>$sequence,'evaluationPeriod'=>$evaluationPeriod]);
            }
            $previousGeneralAverageResults['ep'.$evaluationPeriod->getId()] = $this->markPeriodGeneralAverageCalculatedRepository->findOneBy(['student'=>$studentRegistration,'evaluationPeriod'=>$evaluationPeriod]);
        }

        $evaluationPeriodsDatas = [];
        if ($this->displaySequencesInBulletins){
            foreach ($this->evaluationPeriodsArray as $evaluationPeriodId => $sequences) {
                $evaluationPeriodDatas = [];
                $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);

                $evaluationPeriodDatas['evaluationPeriod'] = $evaluationPeriod;
                $evaluationPeriodDatas['sequences'] = $sequences;

                $evaluationPeriodsDatas[] = $evaluationPeriodDatas;
            }
        }else{
            foreach ($this->evaluationPeriods as $evaluationPeriod) {
                $evaluationPeriodDatas = [];

                $evaluationPeriodDatas['evaluationPeriod'] = $evaluationPeriod;

                $evaluationPeriodsDatas[] = $evaluationPeriodDatas;
            }
        }

        $totalColumns = 5 + intval($this->calculateSubjectsRanks);

        $totalColumnsSequences = 0;
        if ($this->displaySequencesInBulletins){
            $totalColumnsSequences = array_sum(array_map(fn(array $sequences)=>count($sequences),array_column($evaluationPeriodsDatas,'sequences')));
        }

        $totalColumnsSequences += count($this->evaluationPeriods);
        $totalColumns += $totalColumnsSequences;

        $transcriptInfos = [
            'student'=>[
                'student'=> $student,
                'previousGeneralAverageResults'=>$previousGeneralAverageResults
            ],

            'evaluationPeriodsDatas'=>$evaluationPeriodsDatas,
            'disciplineElements'=>$disciplineArray,
            'totalColumns'=>$totalColumns,
            'totalColumnsSequences'=>$totalColumnsSequences,

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
            'displayNumberOfRows'=>$this->displayNumberOfRows,
            'calculateSubjectsRanks'=>$this->calculateSubjectsRanks,
            'showPhotoOnSummary'=>$this->showPhotoOnSummary,
        ];

        $annualResultInfos = [
            'allMarksCalculated' => $allMarksCalculated,
            'classificationInfos' => $classificationInfos,
            'transcriptInfos' => $transcriptInfos,
            'configurations' => $configurations,
            'generalAverageResult'=> $generalAverageResult,
            'positionResult'=> $positionResult,
            'workRemarks'=>$workRemarks,
            'grantTh'=> $markAnnualGeneralAverageCalculated->getGrantThAnnual()
        ];
        return $annualResultInfos;
    }
}