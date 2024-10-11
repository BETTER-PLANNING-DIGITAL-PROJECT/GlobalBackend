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

class AnnualMarkPrimaryTranscriptGetAllMarksCalculatedUtil
{
    public function __construct(
        // Configurations
        private bool                                                    $displaySequencesInBulletins,
        private bool                                                    $displayNumberOfRows,
        private bool $displayNumberOfRowsOnCourses,
        private bool $displayTheClassSizeInRows,
        private bool                                                    $calculateSubjectsRanks,
        private bool                                                    $displayMarksNotEnteredInTheBulletin,
        private bool                                                    $showPhotoOnSummary,

        // Attributs principaux
        private int                                                     $classHeadcount,
        private StudentRegistration                                     $student,
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
        private readonly MarkAnnualCourseCalculatedRepository           $markAnnualCourseCalculatedRepository,
        private readonly MarkAnnualModuleCalculatedRepository           $markAnnualModuleCalculatedRepository,
        private readonly MarkAnnualModuleCalculationRelationRepository  $markAnnualModuleCalculationRelationRepository,
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
        if ($mark === null) {
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

        $markCalculatedFormatted['name'] = $nameuvc;
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
                    $markCalculatedFormatted['ep' . $evaluationPeriodId . 'seq' . $sequence->getId()] = [
                        'mark' => $sequenceMark,
                        'courseStatus' => $sequenceCourseStatus,
                    ];
                    if ($markSequenceCourseCalculated) {
                        $sequenceMark = $markSequenceCourseCalculated->getMark();
                        $markCalculatedFormatted['ep' . $evaluationPeriodId . 'seq' . $sequence->getId()]['mark'] = $sequenceMark;
                        $isCourseSequenceValidated = $markSequenceCourseCalculated->getIsCourseValidated();
                        $sequenceCourseStatus = $markCalculatedFormatted['ep' . $evaluationPeriodId . 'seq' . $sequence->getId()]['courseStatus'] = $isCourseSequenceValidated;
                    }

                    $markCalculatedFormatted['ep' . $evaluationPeriodId . 'seq' . $sequence->getId()]['mark'] = $sequenceMark;
                }

                // Construction de la cellule pour la note du trimestre
                $markPeriodCourseCalculated = $this->markPeriodCourseCalculatedRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration, 'evaluationPeriod' => $evaluationPeriod]);
                $periodMark = 'NC';
                $periodCourseStatus = false;
                $markCalculatedFormatted['ep' . $evaluationPeriodId] = [
                    'mark' => $periodMark,
                    'courseStatus' => $periodCourseStatus,
                ];
                if ($markPeriodCourseCalculated) {
                    $periodMark = $markPeriodCourseCalculated->getMark();
                    $markCalculatedFormatted['ep' . $evaluationPeriodId]['mark'] = $periodMark;
                    $isCoursePeriodValidated = str_contains('mv', $markPeriodCourseCalculated->getIsCourseValidated());
                    $periodCourseStatus = $markCalculatedFormatted['ep' . $evaluationPeriodId]['courseStatus'] = $isCoursePeriodValidated;
                }

                $markCalculatedFormatted['ep' . $evaluationPeriodId]['mark'] = $periodMark;
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
                $markCalculatedFormatted['ep' . $evaluationPeriodId] = [
                    'mark' => $periodMark,
                    'courseStatus' => $periodCourseStatus,
                ];
                if ($markPeriodCourseCalculated) {
                    $periodMark = $markPeriodCourseCalculated->getMark();
                    $markCalculatedFormatted['ep' . $evaluationPeriodId]['mark'] = $periodMark;
                    $isCoursePeriodValidated = str_contains('mv', $markPeriodCourseCalculated->getIsCourseValidated());
                    $periodCourseStatus = $markCalculatedFormatted['ep' . $evaluationPeriodId]['courseStatus'] = $isCoursePeriodValidated;
                }

                $markCalculatedFormatted['ep' . $evaluationPeriodId]['mark'] = $periodMark;
            }
        }

        $markCalculatedFormatted['mark'] = $markShowed;
        $markCalculatedFormatted['coeff'] = $coeff;
        $markCalculatedFormatted['validatedCredit'] = $validatedCredit;
        $markCalculatedFormatted['consideredCredit'] = $consideredCredit;
        $markCalculatedFormatted['courseStatus'] = $isCourseValidated;
        $markCalculatedFormatted['moduleStatus'] = $isModuleEliminated;

        $isClassed = $markAnnualCourseCalculated->getIsClassed();
        // Rang des matieres
        if ($this->calculateSubjectsRanks) {
            $rank = $markAnnualCourseCalculated->getRank();
            $totalCourseStudentsRegistered = $markAnnualCourseCalculated->getTotalCourseStudentsRegistered();
            $rankShowed = isset($rank,$mark) ? ($this->displayNumberOfRowsOnCourses ? $rank.'/'.($this->displayTheClassSizeInRows ? $this->classHeadcount : $totalCourseStudentsRegistered) : $rank) : ($isClassed ? '' : 'not classed');
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

    public function getMarkAnnualModuleCalculatedFormatted(MarkAnnualModuleCalculated|MarkAnnualGeneralAverageCalculated $markAnnualModuleCalculated)
    {
        $coeff = $markAnnualModuleCalculated->getTotalCredit();
        $validatedCredit = $markAnnualModuleCalculated->getTotalCreditValidated();
        $consideredCredit = $markAnnualModuleCalculated->getTotalCreditConsidered();

        $markGrade = $markAnnualModuleCalculated->getGrade();

        $averageGpa = $markAnnualModuleCalculated->getAverageGpa();

        $markCalculatedFormatted = [];

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

    function getAllMarksCalculated(StudentRegistration $student)
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
                $code = $module->getCode();
                $mark = $markAnnualModuleCalculated->getMark();
                $markShowed = $mark;
                if ($mark === null) {
                    $markShowed = $this->displayMarksNotEnteredInTheBulletin ? 'X' : '';
                }

                // Note du module
                $markCalculatedFormatted = $this->getMarkAnnualModuleCalculatedFormatted($markAnnualModuleCalculated);

                $markCalculatedFormatted['name'] = $name;
                $markCalculatedFormatted['code'] = $code;
                if ($this->displaySequencesInBulletins) {
                    // On ajoute les notes des sequences et des periodes
                    foreach ($this->evaluationPeriodsArray as $evaluationPeriodId => $sequences) {
                        $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);

                        foreach ($sequences as $sequence) {
                            $markSequenceModuleCalculated = $this->markSequenceModuleCalculatedRepository->findOneBy(['student' => $this->student, 'module' => $module, 'sequence' => $sequence, 'evaluationPeriod' => $evaluationPeriod]);
                            $sequenceMark = 'NC';
                            $sequenceModuleStatus = false;
                            $markCalculatedFormatted['ep' . $evaluationPeriodId . 'seq' . $sequence->getId()] = [
                                'mark' => $sequenceMark,
                                'courseStatus' => $sequenceModuleStatus,
                            ];
                            if ($markSequenceModuleCalculated) {
                                $sequenceMark = $markSequenceModuleCalculated->getMark();
                                $markCalculatedFormatted['ep' . $evaluationPeriodId . 'seq' . $sequence->getId()]['mark'] = $sequenceMark;
                                $isModuleSequenceValidated = true;//$markSequenceModuleCalculated->getIsModuleValidated();
                                $sequenceModuleStatus = $markCalculatedFormatted['ep' . $evaluationPeriodId . 'seq' . $sequence->getId()]['courseStatus'] = $isModuleSequenceValidated;
                            }

                            $markCellContent = $sequenceMark;// Les notes des modules s'affiche de la meme maniere validee ou non $getCellContent($sequenceModuleStatus,$sequenceMark);
                            $markCalculatedFormatted['ep' . $evaluationPeriodId . 'seq' . $sequence->getId()]['mark'] = $markCellContent;
                        }

                        // Construction de la cellule pour la note du trimestre
                        $markPeriodModuleCalculated = $this->markPeriodModuleCalculatedRepository->findOneBy(['student' => $this->student, 'module' => $module, 'evaluationPeriod' => $evaluationPeriod]);
                        $periodMark = 'NC';
                        $periodModuleStatus = false;
                        $markCalculatedFormatted['ep' . $evaluationPeriodId] = [
                            'mark' => $periodMark,
                            'courseStatus' => $periodModuleStatus,
                        ];
                        if ($markPeriodModuleCalculated) {
                            $periodMark = $markPeriodModuleCalculated->getMark();
                            $markCalculatedFormatted['ep' . $evaluationPeriodId]['mark'] = $periodMark;
                            $isModulePeriodValidated = true; //str_contains('mv',$markPeriodModuleCalculated->getIsModuleValidated());
                            $periodModuleStatus = $markCalculatedFormatted['ep' . $evaluationPeriodId]['courseStatus'] = $isModulePeriodValidated;
                        }

                        $markCellContent = $periodMark;//Les notes des modules s'affiche de la meme maniere validee ou non $getCellContent($periodModuleStatus,$periodMark);
                        $markCalculatedFormatted['ep' . $evaluationPeriodId]['mark'] = $markCellContent;
                    }
                } else {
                    // On ajoute les notes des periodes
                    foreach ($this->evaluationPeriods as $evaluationPeriod) {
                        // Construction de la cellule pour la note du trimestre
                        $evaluationPeriodId = $evaluationPeriod->getId();
                        $markPeriodModuleCalculated = $this->markPeriodModuleCalculatedRepository->findOneBy(['student' => $student, 'module' => $module, 'evaluationPeriod' => $evaluationPeriod]);
                        $periodMark = 'NC';
                        $periodModuleStatus = false;
                        $markCalculatedFormatted['ep' . $evaluationPeriodId] = [
                            'mark' => $periodMark,
                            'courseStatus' => $periodModuleStatus,
                        ];
                        if ($markPeriodModuleCalculated) {
                            $periodMark = $markPeriodModuleCalculated->getMark();
                            $markCalculatedFormatted['ep' . $evaluationPeriodId]['mark'] = $periodMark;
                            $isModulePeriodValidated = $periodMark; //str_contains('mv',$markPeriodModuleCalculated->getIsModuleValidated());
                            $periodModuleStatus = $markCalculatedFormatted['ep' . $evaluationPeriodId]['courseStatus'] = $isModulePeriodValidated;
                        }

                        $markCellContent = $periodMark;//$getCellContent($periodModuleStatus,$periodMark);
                        $markCalculatedFormatted['ep' . $evaluationPeriodId]['mark'] = $markCellContent;
                    }
                }
                $markCalculatedFormatted['mark'] = $markShowed;

                $markCalculatedFormatted['isModule'] = true;
                $allMarksCalculated[] = $markCalculatedFormatted;

                $markAnnualModuleCalculationRelations = $this->markAnnualModuleCalculationRelationRepository->findBy($criteriaModule);
                $count = count($markAnnualModuleCalculationRelations);
                foreach ($markAnnualModuleCalculationRelations as $i => $markAnnualModuleCalculationRelation) {
                    $markAnnualCourseCalculated = $markAnnualModuleCalculationRelation->getMarkCourseCalculated();
                    $markCalculatedFormatted = $this->getMarkAnnualCourseCalculatedFormatted($markAnnualCourseCalculated);
                    $markCalculatedFormatted['isFirstCourse'] = $i === 0;
                    $markCalculatedFormatted['courseModuleNumber'] = $count;
                    $allMarksCalculated[] = $markCalculatedFormatted;
                }
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
            foreach ($markAnnualCourseCalculateds as $i => $markAnnualCourseCalculated) {
                $markCalculatedFormatted = $this->getMarkAnnualCourseCalculatedFormatted($markAnnualCourseCalculated);
                $markCalculatedFormatted['isFirstCourse'] = $i === 0;
                $markCalculatedFormatted['courseModuleNumber'] = $count;
                $allMarksCalculated[] = $markCalculatedFormatted;

            }
        }

        $generalAverageResult = [];
        $markCalculatedPositionFormatted = [];
        $markCalculatedTotalFormatted = [];
        $positionResult = $totalResult = [];
        $isClassed = false;
        if ($markAnnualGeneralAverageCalculated) {
            $markCalculatedFormatted = $this->getMarkAnnualModuleCalculatedFormatted($markAnnualGeneralAverageCalculated);
            $markCalculatedPositionFormatted = $markCalculatedTotalFormatted = $markCalculatedFormatted;
            $markCalculatedFormatted['name'] = 'general average';
            $markCalculatedPositionFormatted['name'] = 'position';
            $markCalculatedTotalFormatted['name'] = 'total';

            if ($this->displaySequencesInBulletins) {
                foreach ($this->evaluationPeriodsArray as $evaluationPeriodId => $sequences) {
                    $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);
                    // Moyennes generales des sequences
                    foreach ($sequences as $sequence) {
                        $markSequenceGeneralAverageCalculated = $this->markSequenceGeneralAverageCalculatedRepository->findOneBy(['student' => $student, 'sequence' => $sequence, 'evaluationPeriod' => $evaluationPeriod]);
                        $seqId = 'ep' . $evaluationPeriodId . 'seq' . $sequence->getId();
                        $markCalculatedFormatted[$seqId] = 'NC';
                        $markCalculatedPositionFormatted[$seqId] = $markCalculatedTotalFormatted[$seqId] = '';

                        if ($markSequenceGeneralAverageCalculated) {
                            $periodAverage = $markSequenceGeneralAverageCalculated->getAverage();
                            $markCalculatedFormatted[$seqId] = $periodAverage;
                            $total = $markSequenceGeneralAverageCalculated->getTotal();
                            $markCalculatedTotalFormatted[$seqId] = $total;

                            $isClassed = $markSequenceGeneralAverageCalculated->getIsClassed();
                            $rank = $markSequenceGeneralAverageCalculated->getRank();
                            $totalStudentsClassed = $markSequenceGeneralAverageCalculated->getTotalStudentsClassed();
                            $rankShowed = isset($rank,$periodAverage) ? ($this->displayNumberOfRows ? $rank.'/'.($this->displayTheClassSizeInRows ? $this->classHeadcount : $totalStudentsClassed) : $rank) : ($isClassed ? '' : 'not classed');

                            $markCalculatedPositionFormatted[$seqId] = $rankShowed;
                        }
                    }

                    // Moyenne generale des periodes
                    $markPeriodGeneralAverageCalculated = $this->markPeriodGeneralAverageCalculatedRepository->findOneBy(['student' => $student, 'evaluationPeriod' => $evaluationPeriod]);
                    $epId = 'ep' . $evaluationPeriodId;
                    $markCalculatedFormatted[$epId] = 'NC';
                    $markCalculatedPositionFormatted[$epId] = $markCalculatedTotalFormatted[$epId] = '';

                    if ($markPeriodGeneralAverageCalculated) {
                        $periodAverage = $markPeriodGeneralAverageCalculated->getAverage();
                        $markCalculatedFormatted[$epId] = $periodAverage;
                        $total = $markPeriodGeneralAverageCalculated->getTotal();
                        $markCalculatedTotalFormatted[$epId] = $total;

                        $isClassed = $markPeriodGeneralAverageCalculated->getIsClassed();
                        $rank = $markPeriodGeneralAverageCalculated->getRank();
                        $totalStudentsClassed = $markPeriodGeneralAverageCalculated->getTotalStudentsClassed();
                        $rankShowed = isset($rank,$periodAverage) ? ($this->displayNumberOfRows ? $rank.'/'.($this->displayTheClassSizeInRows ? $this->classHeadcount : $totalStudentsClassed) : $rank) : ($isClassed ? '' : 'not classed');

                        $markCalculatedPositionFormatted[$epId] = $rankShowed;
                    }
                }
            } else {
                // Moyenne generale des periodes
                $markPeriodGeneralAverageCalculated = $this->markPeriodGeneralAverageCalculatedRepository->findOneBy(['student' => $student, 'evaluationPeriod' => $evaluationPeriod]);
                $epId = 'ep' . $evaluationPeriodId;
                $markCalculatedFormatted[$epId] = 'NC';
                $markCalculatedPositionFormatted[$epId] = $markCalculatedTotalFormatted[$epId] = '';

                if ($markPeriodGeneralAverageCalculated) {
                    $periodAverage = $markPeriodGeneralAverageCalculated->getAverage();
                    $markCalculatedFormatted[$epId] = $periodAverage;
                    $total = $markPeriodGeneralAverageCalculated->getTotal();
                    $markCalculatedTotalFormatted[$epId] = $total;

                    $isClassed = $markPeriodGeneralAverageCalculated->getIsClassed();
                    $rank = $markPeriodGeneralAverageCalculated->getRank();
                    $totalStudentsClassed = $markPeriodGeneralAverageCalculated->getTotalStudentsClassed();
                    $rankShowed = isset($rank,$periodAverage) ? ($this->displayNumberOfRows ? $rank.'/'.($this->displayTheClassSizeInRows ? $this->classHeadcount : $totalStudentsClassed) : $rank) : ($isClassed ? '' : 'not classed');

                    $markCalculatedPositionFormatted[$epId] = $rankShowed;
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
            $rankShowed = isset($rank,$average) ? ($this->displayNumberOfRows ? $rank.'/'.($this->displayTheClassSizeInRows ? $this->classHeadcount : $totalStudentsClassed) : $rank) : ($isClassed ? '' : 'not classed');

            $markCalculatedFormatted['rank'] = $rankShowed;
            $markCalculatedPositionFormatted['mark'] = $rankShowed;

            $total = $markAnnualGeneralAverageCalculated->getTotal();
            $markCalculatedTotalFormatted['mark'] = $total;

            $school = $markAnnualGeneralAverageCalculated->getSchool();
            $markGrade = $this->markGradeRepository->findOneBy(['school' => $school], ['gpa' => 'DESC']);

            $averageGpa = $markCalculatedFormatted['averageGpa'];
            if (!isset($averageGpa)) {
//                if (isset($markGrade)) $averageGpa .= '/'.$markGrade->getGpa();
                $averageGpa = 'X';
            }

            $markCalculatedFormatted['maxGpa'] = !isset($markGrade) ? '' : ' / ' . $markGrade->getGpa();

            $markCalculatedFormatted['averageGpa'] = $averageGpa;
            $generalAverageResult = $markCalculatedFormatted;
            $positionResult = $markCalculatedPositionFormatted;
            $totalResult = $markCalculatedTotalFormatted;
        }
        $classificationInfos = [];
        $classificationInfos['numberOfAttendedCourses'] = $markAnnualGeneralAverageCalculated->getNumberOfAttendedCourses();
        $classificationInfos['numberOfComposedCourses'] = $markAnnualGeneralAverageCalculated->getNumberOfComposedCourses();
        $classificationInfos['totalOfCreditsAttended'] = $markAnnualGeneralAverageCalculated->getTotalOfCreditsAttended();
        $classificationInfos['totalOfCreditsComposed'] = $markAnnualGeneralAverageCalculated->getTotalOfCreditsComposed();
        $classificationInfos['percentageSubjectNumber'] = $markAnnualGeneralAverageCalculated->getPercentageSubjectNumber();
        $classificationInfos['percentageTotalCoefficient'] = $markAnnualGeneralAverageCalculated->getPercentageTotalCoefficient();
        $classificationInfos['isClassed'] = $isClassed;

        $remarks = ['blameWork' => 'blame work', 'warningWork' => 'warning work', 'grantEncouragement' => 'encouragements', 'grantCongratulation' => 'congratulations'];

        $workRemarks = [];
        foreach ($remarks as $remark => $remarkLower) {
            $getterMethod = ucfirst($remark);
            if ($markAnnualGeneralAverageCalculated->{'get' . $getterMethod}()) {
                $workRemarks[] = $remarkLower;
            }
        }

        // Informations des periodes d'evaluation et des sequences de l'annee
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

        $totalColumns = 4 + intval($this->calculateSubjectsRanks);

        $totalColumnsSequences = 0;
        if ($this->displaySequencesInBulletins){
            $totalColumnsSequences = array_sum(array_map(fn(array $sequences)=>count($sequences),array_column($evaluationPeriodsDatas,'sequences')));
        }

        $totalColumnsSequences += count($this->evaluationPeriods);
        $totalColumns += $totalColumnsSequences;

        // Informations pour les releves de notes
        $class = $student->getCurrentClass();
        $institution = $student->getInstitution();
        $transcriptInfos = [
            'student' => $student->getStudent(),
            'evaluationPeriodsDatas'=>$evaluationPeriodsDatas,
            'totalColumns'=>$totalColumns,
            'totalColumnsSequences'=>$totalColumnsSequences,
            'evaluationPeriodsArray' => $this->evaluationPeriodsArray,
            'class' => ['class'=>$class,'headcount'=>$this->classHeadcount],
            'level' => $class->getLevel(),
            'speciality' => $class->getSpeciality(),
            'year' => $student->getCurrentYear(),
            'institution' => $institution,
            'country' => $institution->getCountry() ? $institution->getCountry()->getName() : ''
        ];
        $configurations = [
            'displaySequencesInBulletins' => $this->displaySequencesInBulletins,
            'calculateSubjectsRanks' => $this->calculateSubjectsRanks,
            'showPhotoOnSummary' => $this->showPhotoOnSummary,
        ];

        $annualResultInfos = [
            'allMarksCalculated' => $allMarksCalculated,
            'classificationInfos' => $classificationInfos,
            'transcriptInfos' => $transcriptInfos,
            'configurations' => $configurations,
            'generalAverageResult' => $generalAverageResult,
            'positionResult' => $positionResult,
            'totalResult' => $totalResult,
            'workRemarks' => $workRemarks,
            'grantTh' => $markAnnualGeneralAverageCalculated->getGrantThAnnual()
        ];
        if ($this->displaySequencesInBulletins) {
            $annualResultInfos['sequences'] = $this->sequences;
        }
        return $annualResultInfos;
    }
}