<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Course\PrimaryAndSecondary;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Generation\Student\Course\AnnualMarkGenerationCourseUtil;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculatedRepository;

// Classe contenant toutes les fonctions concernant le calcul des notes pour une sequence
class AnnualMarkCalculationCoursesPrimaryAndSecondaryUtil
{
// Configurations
//    private bool $isEliminationCourseActivated = false;

    private string $calculateAnnualCourseMarksMethod;

    public function __construct(
        // Configurations
//        private ?float $eliminateMark,
        string                                                             $finalMarkFormula,
          private ?float $validationBase,
        // Attributs principaux
        private readonly array                                             $evaluationPeriodsArray,

        // Utils
        private AnnualMarkGenerationCourseUtil  $annualMarkGenerationCourseUtil,
        private AnnualMarkCalculationCoursePrimaryAndSecondaryUtil $annualMarkCalculationCourseUtil,

        // Repository
        private EvaluationPeriodRepository                                 $evaluationPeriodRepository,
        private MarkSequenceGeneralAverageCalculatedRepository             $markSequenceGeneralAverageCalculatedRepository,
        private MarkPeriodGeneralAverageCalculatedRepository               $markPeriodGeneralAverageCalculatedRepository
    )
    {
//        $this->isEliminationCourseActivated = $this->sequenceMarkCalculationUtil->isEliminationCourseActivated();
        $this->calculateAnnualCourseMarksMethod = 'calculateAnnualCourseMarks' . $finalMarkFormula;
    }

    // Getters & setters
    public function getAnnualMarkCalculationCourseUtil(): AnnualMarkCalculationCoursePrimaryAndSecondaryUtil
    {
        return $this->annualMarkCalculationCourseUtil;
    }

    public function setAnnualMarkCalculationCourseUtil(AnnualMarkCalculationCoursePrimaryAndSecondaryUtil $annualMarkCalculationCourseUtil): AnnualMarkCalculationCoursesPrimaryAndSecondaryUtil
    {
        $this->annualMarkCalculationCourseUtil = $annualMarkCalculationCourseUtil;
        return $this;
    }

    public function getAnnualMarkGenerationCourseUtil(): AnnualMarkGenerationCourseUtil
    {
        return $this->annualMarkGenerationCourseUtil;
    }

    public function setAnnualMarkGenerationCourseUtil(AnnualMarkGenerationCourseUtil $annualMarkGenerationCourseUtil): AnnualMarkCalculationCoursesPrimaryAndSecondaryUtil
    {
        $this->annualMarkGenerationCourseUtil = $annualMarkGenerationCourseUtil;
        return $this;
    }

    public function getCourses(StudentRegistration $student)
    {
        return $this->annualMarkCalculationCourseUtil->getCourses($student);
    }

    function getCourseMarks(StudentCourseRegistration $studentCourseRegistration)
    {
        $markCourseCalculateds = $this->annualMarkCalculationCourseUtil->getMarks($studentCourseRegistration);
        return $markCourseCalculateds;
    }

    // Calculer la note d'une period d'une matiere en considerant les type de notes
    /**
     * @param mixed $studentCourseRegistration
     * @return float|null
     */
    public function calculateAnnualCourseMark(StudentCourseRegistration $studentCourseRegistration): array
    {
        $courseMarks = $this->getCourseMarks($studentCourseRegistration);
        $mark = $this->annualMarkCalculationCourseUtil->calculateMark($courseMarks);
        return ['mark'=>$mark,'courseMarks'=>$courseMarks];
    }

    function calculateAnnualCourseMarks(array $studentCourseRegistrations): array
    {
        return $this->{$this->calculateAnnualCourseMarksMethod}($studentCourseRegistrations);
    }

    // Calculer la note d'une sequence des matieres en considerant les type de notes
    // La note annuelle d'une matiere est la moyenne des notes des sequences
    // halfYearAverageFormula = 1
    function calculateAnnualCourseMarks1(array $studentCourseRegistrations): array
    {
        /*$markCreditFn = $this->activateWeightingsPerAssignment ? function (StudentCourseRegistration $studentCourseRegistration) {
            $sequenceMarks = $this->markRepository->findBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $this->sequence]);
            $marks = array_map(fn(Mark $sequenceMark) => ['mark' => $sequenceMark->getMark(), 'credit' => $sequenceMark->getNoteType() ? $sequenceMark->getNoteType()->getWeighting() : 1], $sequenceMarks);
            return $marks;
        } : function (StudentCourseRegistration $studentCourseRegistration) {
            $sequenceMarks = $this->markRepository->findBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $this->sequence]);
            $marks = array_map(fn(Mark $sequenceMark) => ['mark' => $sequenceMark->getMark(), 'credit' => 1], $sequenceMarks);
            return $marks;
        };*/

        $markAnnualCoursesCalculated = [];
        $moduleMarkAnnualCoursesCalculated = [];
        $notModuleMarkAnnualCoursesCalculated = [];

        /*if ($this->isEliminationActivated) {
            foreach ($studentCourseRegistrations as $studentCourseRegistration) {
                $markExists = $this->markRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $this->sequence]) !== null;
                if ($markExists) {
                    $markResult = $this->calculatePeriodCourseMark($studentCourseRegistration);
                    $mark = $markResult['mark'];
                    $isEliminated = true;
                    $isCourseValidated = false;
                    $classProgram = $studentCourseRegistration->getClassProgram();
//                    dd($markResult,$markDatas,$mark);
                    $sequenceMarks = $markResult['sequenceMarks'];
                    $validationBase = $classProgram->getValidationBase();

                    if ($mark) {
                        $isEliminated = $mark < $this->eliminateMark;
                        $isCourseValidated = !$isEliminated && $validationBase && $mark >= $validationBase;
                    }

                    $marksResult[] = ['studentCourseRegistration' => $studentCourseRegistration, 'mark' => $mark, 'base' => 20, 'eliminated' => $isEliminated, 'validated' => $isCourseValidated,'classProgram'=>$classProgram];
                    $coeff = $classProgram->getCoeff();
                    $markAnnualCourseCalculated = $this->sequenceMarkGenerationStudentCourseUtil->generatePeriodCourseCalculatedElimination($studentCourseRegistration,$mark,20,$isCourseValidated,$isEliminated,$sequenceMarks,$classProgram,$this->eliminateMark, $validationBase, $coeff);
                    $markAnnualCoursesCalculated[] = $markAnnualCourseCalculated;
                }
            }
        } else {*/
        foreach ($studentCourseRegistrations as $studentCourseRegistration) {
            $markExists = $this->annualMarkCalculationCourseUtil->markExists($studentCourseRegistration,$studentCourseRegistrations);
            if ($markExists){
                $markResult = $this->calculateAnnualCourseMark($studentCourseRegistration);
                $mark = $markResult['mark'];
                $isCourseValidated = 'v';
                $classProgram = $studentCourseRegistration->getClassProgram();
                $courseMarks = $markResult['courseMarks'];

                if (isset($mark)) {
                    $isCourseValidated = !isset($this->validationBase) || $mark >= $this->validationBase ? 'v' : 'nv';
                }

                $module = $studentCourseRegistration->getModule();

//                    $marksResult[] = ['studentCourseRegistration' => $studentCourseRegistration, 'mark' => $mark, 'base' => 20, 'eliminated' => false, 'validated' => $isCourseValidated, 'sequenceMarks' => $sequenceMarks,'classProgram'=>$classProgram];
                $coeff = $classProgram->getCoeff();
                $markAnnualCourseCalculated = $this->annualMarkGenerationCourseUtil->generateAnnualCourseCalculated($mark, $isCourseValidated, $courseMarks, $classProgram, $this->validationBase, $coeff);
                if ($module) $moduleMarkAnnualCoursesCalculated[$module->getId()][] = $markAnnualCourseCalculated;
                else $notModuleMarkAnnualCoursesCalculated[] = $markAnnualCourseCalculated;

//                $markAnnualCourseCalculated = $this->getCourseMarks($studentCourseRegistration);
                $markAnnualCoursesCalculated[] = $markAnnualCourseCalculated;
            }
        }
//        }
        $marksResult = [
            // Toutes les notes
            'markAnnualCoursesCalculated' => $markAnnualCoursesCalculated,

            // Notes dans les modules
            'moduleMarkAnnualCoursesCalculated' => $moduleMarkAnnualCoursesCalculated,

            // Notes sans les modules
            'notModuleMarkAnnualCoursesCalculated' => $notModuleMarkAnnualCoursesCalculated
        ];
        return $marksResult;
    }

    // Calculer la note d'une sequence des matieres en considerant les type de notes
    // halfYearAverageFormula = 2
    function calculateAnnualCourseMarks2(array $studentCourseRegistrations): array
    {
        /*$markCreditFn = $this->activateWeightingsPerAssignment ? function (StudentCourseRegistration $studentCourseRegistration) {
            $courseMarks = $this->markRepository->findBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $this->sequence]);
            $marks = array_map(fn(Mark $sequenceMark) => ['mark' => $sequenceMark->getMark(), 'credit' => $sequenceMark->getNoteType() ? $sequenceMark->getNoteType()->getWeighting() : 1], $courseMarks);
            return $marks;
        } : function (StudentCourseRegistration $studentCourseRegistration) {
            $courseMarks = $this->markRepository->findBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $this->sequence]);
            $marks = array_map(fn(Mark $sequenceMark) => ['mark' => $sequenceMark->getMark(), 'credit' => 1], $courseMarks);
            return $marks;
        };*/

        $markAnnualCoursesCalculated = [];
        $moduleMarkAnnualCoursesCalculated = [];
        $notModuleMarkAnnualCoursesCalculated = [];

        /*if ($this->isEliminationActivated) {
            foreach ($studentCourseRegistrations as $studentCourseRegistration) {
                $markExists = $this->markRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $this->sequence]) !== null;
                if ($markExists) {
                    $markResult = $this->calculatePeriodCourseMark($studentCourseRegistration);
                    $mark = $markResult['mark'];
                    $isEliminated = true;
                    $isCourseValidated = false;
                    $classProgram = $studentCourseRegistration->getClassProgram();
//                    dd($markResult,$markDatas,$mark);
                    $courseMarks = $markResult['courseMarks'];
                    $validationBase = $classProgram->getValidationBase();

                    if ($mark) {
                        $isEliminated = $mark < $this->eliminateMark;
                        $isCourseValidated = !$isEliminated && $validationBase && $mark >= $validationBase;
                    }

                    $marksResult[] = ['studentCourseRegistration' => $studentCourseRegistration, 'mark' => $mark, 'base' => 20, 'eliminated' => $isEliminated, 'validated' => $isCourseValidated,'classProgram'=>$classProgram];
                    $coeff = $classProgram->getCoeff();
                    $markAnnualCourseCalculated = $this->sequenceMarkGenerationStudentCourseUtil->generatePeriodCourseCalculatedElimination($studentCourseRegistration,$mark,20,$isCourseValidated,$isEliminated,$courseMarks,$classProgram,$this->eliminateMark, $validationBase, $coeff);
                    $markAnnualCoursesCalculated[] = $markAnnualCourseCalculated;
                }
            }
        } else {*/
        foreach ($studentCourseRegistrations as $studentCourseRegistration) {
            $markExists = $this->annualMarkCalculationCourseUtil->markExists($studentCourseRegistration);
            if ($markExists) {
                $markResult = $this->calculateAnnualCourseMark($studentCourseRegistration);
                $mark = $markResult['mark'];
                $isCourseValidated = false;
                $classProgram = $studentCourseRegistration->getClassProgram();
                $courseMarks = $markResult['courseMarks'];

                if ($mark) {
                    $isCourseValidated = !$this->validationBase || $mark >= $this->validationBase ? 'v' : 'nv';
                }

                $module = $studentCourseRegistration->getModule();

//                    $marksResult[] = ['studentCourseRegistration' => $studentCourseRegistration, 'mark' => $mark, 'base' => 20, 'eliminated' => false, 'validated' => $isCourseValidated, 'courseMarks' => $courseMarks,'classProgram'=>$classProgram];
                $coeff = $classProgram->getCoeff();
                $markAnnualCourseCalculated = $this->annualMarkGenerationCourseUtil->generateAnnualCourseCalculated( $mark,  $isCourseValidated, $courseMarks, $classProgram, $this->validationBase, $coeff);
                if ($module) $moduleMarkAnnualCoursesCalculated[$module->getId()][] = $markAnnualCourseCalculated;
                else $notModuleMarkAnnualCoursesCalculated[] = $markAnnualCourseCalculated;

//                    $markAnnualCoursesCalculated[] = $markAnnualCourseCalculated;
            }
        }
//        }
        $student = $studentCourseRegistrations[0]->getStudRegistration();

        // Dans ce cas si, je redefenit le tableau des notes calculees des matieres des periodes car je calculais ensuite je generais directement
        // C'est pour cela qu'on peut voir que je renvoie le tableau des notes calculees des matieres avec les moyennes generales mais ca calcule toujours la moyennes generale du semestre
        // J'ecris ce commentaire ici car moi meme ca me fait lap car je suis en train de reflechir comment est ce que le code marche alors que moi meme je ne comprends pas ou est ce que je renvoie les moyennes generales des sequences pour le calcul de la moyenne generale de la periode
        // M.Herve avait raison j aime vraiment beaucoup les codes troooop compliques , le code c'est la magie. ;)
        // 20/05/2024 a 17:06

        foreach ($this->evaluationPeriodsArray as $evaluationPeriodId=> $sequences) {
            $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);
            foreach ($sequences as $sequence) {
                $markAnnualCoursesCalculated[] = $this->markSequenceGeneralAverageCalculatedRepository->findOneBy(['sequence' => $sequence, 'student' => $student, 'evaluationPeriod' => $evaluationPeriod]);
            }
        }

        $marksResult = [
            // Toutes les moyennes generales
            // Je renvoie ici car la logique est deja definie par rapport a un tableau de notes calculees de matiere (cf formule 1)
            'markAnnualCoursesCalculated' => $markAnnualCoursesCalculated,

            // Notes dans les modules
            'moduleMarkAnnualCoursesCalculated' => $moduleMarkAnnualCoursesCalculated,

            // Notes sans les modules
            'notModuleMarkAnnualCoursesCalculated' => $notModuleMarkAnnualCoursesCalculated
        ];
        return $marksResult;
    }

    // Calculer la note d'une sequence des matieres en considerant les type de notes
    // halfYearAverageFormula = 3
    function calculateAnnualCourseMarks3(array $studentCourseRegistrations): array
    {
        /*$markCreditFn = $this->activateWeightingsPerAssignment ? function (StudentCourseRegistration $studentCourseRegistration) {
            $courseMarks = $this->markRepository->findBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $this->sequence]);
            $marks = array_map(fn(Mark $sequenceMark) => ['mark' => $sequenceMark->getMark(), 'credit' => $sequenceMark->getNoteType() ? $sequenceMark->getNoteType()->getWeighting() : 1], $courseMarks);
            return $marks;
        } : function (StudentCourseRegistration $studentCourseRegistration) {
            $courseMarks = $this->markRepository->findBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $this->sequence]);
            $marks = array_map(fn(Mark $sequenceMark) => ['mark' => $sequenceMark->getMark(), 'credit' => 1], $courseMarks);
            return $marks;
        };*/

        $markAnnualCoursesCalculated = [];
        $moduleMarkAnnualCoursesCalculated = [];
        $notModuleMarkAnnualCoursesCalculated = [];

        /*if ($this->isEliminationActivated) {
            foreach ($studentCourseRegistrations as $studentCourseRegistration) {
                $markExists = $this->markRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration, 'sequence' => $this->sequence]) !== null;
                if ($markExists) {
                    $markResult = $this->calculatePeriodCourseMark($studentCourseRegistration);
                    $mark = $markResult['mark'];
                    $isEliminated = true;
                    $isCourseValidated = false;
                    $classProgram = $studentCourseRegistration->getClassProgram();
//                    dd($markResult,$markDatas,$mark);
                    $courseMarks = $markResult['courseMarks'];
                    $validationBase = $classProgram->getValidationBase();

                    if ($mark) {
                        $isEliminated = $mark < $this->eliminateMark;
                        $isCourseValidated = !$isEliminated && $validationBase && $mark >= $validationBase;
                    }

                    $marksResult[] = ['studentCourseRegistration' => $studentCourseRegistration, 'mark' => $mark, 'base' => 20, 'eliminated' => $isEliminated, 'validated' => $isCourseValidated,'classProgram'=>$classProgram];
                    $coeff = $classProgram->getCoeff();
                    $markAnnualCourseCalculated = $this->sequenceMarkGenerationStudentCourseUtil->generatePeriodCourseCalculatedElimination($studentCourseRegistration,$mark,20,$isCourseValidated,$isEliminated,$courseMarks,$classProgram,$this->eliminateMark, $validationBase, $coeff);
                    $markAnnualCoursesCalculated[] = $markAnnualCourseCalculated;
                }
            }
        } else {*/
        foreach ($studentCourseRegistrations as $studentCourseRegistration) {
            $markExists = $this->annualMarkCalculationCourseUtil->markExists($studentCourseRegistration);
            if ($markExists) {
                $markResult = $this->calculateAnnualCourseMark($studentCourseRegistration);
                $mark = $markResult['mark'];
                $isCourseValidated = false;
                $classProgram = $studentCourseRegistration->getClassProgram();
                $courseMarks = $markResult['courseMarks'];

                if ($mark) {
                    $isCourseValidated = !$this->validationBase || $mark >= $this->validationBase ? 'v' : 'nv';
                }

                $module = $studentCourseRegistration->getModule();

//                    $marksResult[] = ['studentCourseRegistration' => $studentCourseRegistration, 'mark' => $mark, 'base' => 20, 'eliminated' => false, 'validated' => $isCourseValidated, 'courseMarks' => $courseMarks,'classProgram'=>$classProgram];
                $coeff = $classProgram->getCoeff();
                $markAnnualCourseCalculated = $this->annualMarkGenerationCourseUtil->generateAnnualCourseCalculated($mark,  $isCourseValidated, $courseMarks, $classProgram, $this->validationBase, $coeff);
                if ($module) $moduleMarkAnnualCoursesCalculated[$module->getId()][] = $markAnnualCourseCalculated;
                else $notModuleMarkAnnualCoursesCalculated[] = $markAnnualCourseCalculated;

//                    $markAnnualCoursesCalculated[] = $markAnnualCourseCalculated;
            }
        }
//        }
        $student = $studentCourseRegistrations[0]->getStudRegistration();

        // Dans ce cas si, je redefenit le tableau des notes calculees des matieres des periodes car je calculais ensuite je generais directement
        // C'est pour cela qu'on peut voir que je renvoie le tableau des notes calculees des matieres avec les moyennes generales mais ca calcule toujours la moyennes generale du semestre
        // J'ecris ce commentaire ici car moi meme ca me fait lap car je suis en train de reflechir comment est ce que le code marche alors que moi meme je ne comprends pas ou est ce que je renvoie les moyennes generales des sequences pour le calcul de la moyenne generale de la periode
        // M.Herve avait raison j aime vraiment beaucoup les codes troooop compliques , le code c'est la magie. ;)
        // 20/05/2024 a 17:06

        foreach ($this->evaluationPeriodsArray as $evaluationPeriodId=> $sequences) {
            $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);
            $markAnnualCoursesCalculated[] = $this->markPeriodGeneralAverageCalculatedRepository->findOneBy(['student' => $student, 'evaluationPeriod' => $evaluationPeriod]);
        }

        $marksResult = [
            // Toutes les moyennes generales
            // Je renvoie ici car la logique est deja definie par rapport a un tableau de notes calculees de matiere (cf formule 1)
            'markAnnualCoursesCalculated' => $markAnnualCoursesCalculated,

            // Notes dans les modules
            'moduleMarkAnnualCoursesCalculated' => $moduleMarkAnnualCoursesCalculated,

            // Notes sans les modules
            'notModuleMarkAnnualCoursesCalculated' => $notModuleMarkAnnualCoursesCalculated
        ];
        return $marksResult;
    }
}