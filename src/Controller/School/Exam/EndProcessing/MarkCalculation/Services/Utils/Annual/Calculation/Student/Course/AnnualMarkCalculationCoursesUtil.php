<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Course;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Course\AnnualMarkCalculationCourseUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Calculation\Student\Course\PeriodMarkCalculationCourseUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Generation\Student\Course\PeriodMarkGenerationCourseUtil;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Mark;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculatedRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;

// Classe contenant toutes les fonctions concernant le calcul des notes pour une sequence
class AnnualMarkCalculationCoursesUtil
{
// Configurations
//    private bool $isEliminationCourseActivated = false;

    private string $calculateAnnualCourseMarksMethod;

    public function __construct(
        // Configurations
//        private ?float $eliminateMark,
        string                                                 $finalAverageFormula,
//          private ?float $validationBase,
        // Attributs principaux
        private readonly array                                 $evaluationPeriodsArray,

        // Utils
//        private PeriodMarkGenerationCourseUtil  $periodMarkGenerationCourseUtil,
        private AnnualMarkCalculationCourseUtil                $annualMarkCalculationCourseUtil,

        // Repository
        private EvaluationPeriodRepository                     $evaluationPeriodRepository,
        private MarkSequenceGeneralAverageCalculatedRepository $markSequenceGeneralAverageCalculatedRepository,
        private MarkPeriodGeneralAverageCalculatedRepository   $markPeriodGeneralAverageCalculatedRepository
    )
    {
//        $this->isEliminationCourseActivated = $this->sequenceMarkCalculationUtil->isEliminationCourseActivated();
        $this->calculateAnnualCourseMarksMethod = 'calculateAnnualCourseMarks' . $finalAverageFormula;
    }

    // Getters & setters
    public function getAnnualMarkCalculationCourseUtil(): AnnualMarkCalculationCourseUtil
    {
        return $this->annualMarkCalculationCourseUtil;
    }

    public function setAnnualMarkCalculationCourseUtil(AnnualMarkCalculationCourseUtil $annualMarkCalculationCourseUtil): AnnualMarkCalculationCoursesUtil
    {
        $this->annualMarkCalculationCourseUtil = $annualMarkCalculationCourseUtil;
        return $this;
    }

    public function getCourses(StudentRegistration $student)
    {
        return $this->annualMarkCalculationCourseUtil->getCourses($student);
    }


    function getCourseMarks(StudentCourseRegistration $studentCourseRegistration)
    {
        $markPeriodCourseCalculated = $this->annualMarkCalculationCourseUtil->getMarks($studentCourseRegistration);
        return $markPeriodCourseCalculated;
    }
//
//    // Calculer la note d'une period d'une matiere en considerant les type de notes
//    /**
//     * @param mixed $studentCourseRegistration
//     * @return float|null
//     */
//    public function calculatePeriodCourseMark(StudentCourseRegistration $studentCourseRegistration): array
//    {
//        $sequenceMarks = $this->getCourseMarks($studentCourseRegistration);
//        $mark = $this->periodMarkCalculationCourseUtil->calculateMark($sequenceMarks);
//        return ['mark'=>$mark,'sequenceMarks'=>$sequenceMarks];
//    }

    function calculateAnnualCourseMarks(array $studentCourseRegistrations): array
    {
        return $this->{$this->calculateAnnualCourseMarksMethod}($studentCourseRegistrations);
    }

    // Calculer la note d'une sequence des matieres en considerant les type de notes
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

        $markPeriodCoursesCalculated = [];
        $moduleMarkPeriodCoursesCalculated = [];
        $notModuleMarkPeriodCoursesCalculated = [];

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
                    $markPeriodCourseCalculated = $this->sequenceMarkGenerationStudentCourseUtil->generatePeriodCourseCalculatedElimination($studentCourseRegistration,$mark,20,$isCourseValidated,$isEliminated,$sequenceMarks,$classProgram,$this->eliminateMark, $validationBase, $coeff);
                    $markPeriodCoursesCalculated[] = $markPeriodCourseCalculated;
                }
            }
        } else {*/
        foreach ($studentCourseRegistrations as $studentCourseRegistration) {
            $markExists = $this->annualMarkCalculationCourseUtil->markExists($studentCourseRegistration);
            if ($markExists) {
                /*$markResult = $this->calculatePeriodCourseMark($studentCourseRegistration);
                $mark = $markResult['mark'];
                $isCourseValidated = 'v';
                $classProgram = $studentCourseRegistration->getClassProgram();
                $sequenceMarks = $markResult['sequenceMarks'];

                if (isset($mark)) {
                    $isCourseValidated = !isset($this->validationBase) || $mark >= $this->validationBase ? 'v' : 'nv';
                }

                $module = $studentCourseRegistration->getModule();

//                    $marksResult[] = ['studentCourseRegistration' => $studentCourseRegistration, 'mark' => $mark, 'base' => 20, 'eliminated' => false, 'validated' => $isCourseValidated, 'sequenceMarks' => $sequenceMarks,'classProgram'=>$classProgram];
                $coeff = $classProgram->getCoeff();
                $markPeriodCourseCalculated = $this->periodMarkGenerationCourseUtil->generatePeriodCourseCalculated($studentCourseRegistration, $mark, $isCourseValidated, $sequenceMarks, $classProgram, $this->validationBase, $coeff);
                if ($module) $moduleMarkPeriodCoursesCalculated[$module->getId()][] = $markPeriodCourseCalculated;
                else $notModuleMarkPeriodCoursesCalculated[] = $markPeriodCourseCalculated;*/

                $markPeriodCourseCalculated = $this->getCourseMarks($studentCourseRegistration);
                $markPeriodCoursesCalculated[] = $markPeriodCourseCalculated;
            }
        }
//        }
        $marksResult = [
            // Toutes les notes
            'markPeriodCoursesCalculated' => $markPeriodCoursesCalculated,

            // Notes dans les modules
            'moduleMarkPeriodCoursesCalculated' => $moduleMarkPeriodCoursesCalculated,

            // Notes sans les modules
            'notModuleMarkPeriodCoursesCalculated' => $notModuleMarkPeriodCoursesCalculated
        ];
        return $marksResult;
    }

    // Calculer la note d'une sequence des matieres en considerant les type de notes
    // halfYearAverageFormula = 2
    function calculateAnnualCourseMarks2(array $studentCourseRegistrations): array
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

        $markPeriodCoursesCalculated = [];
        $moduleMarkPeriodCoursesCalculated = [];
        $notModuleMarkPeriodCoursesCalculated = [];

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
                    $markPeriodCourseCalculated = $this->sequenceMarkGenerationStudentCourseUtil->generatePeriodCourseCalculatedElimination($studentCourseRegistration,$mark,20,$isCourseValidated,$isEliminated,$sequenceMarks,$classProgram,$this->eliminateMark, $validationBase, $coeff);
                    $markPeriodCoursesCalculated[] = $markPeriodCourseCalculated;
                }
            }
        } else {*/
        /*foreach ($studentCourseRegistrations as $studentCourseRegistration) {
            $markExists = $this->annualMarkCalculationCourseUtil->markExists($studentCourseRegistration);
            if ($markExists) {
                $markResult = $this->calculatePeriodCourseMark($studentCourseRegistration);
                $mark = $markResult['mark'];
                $isCourseValidated = false;
                $classProgram = $studentCourseRegistration->getClassProgram();
                $sequenceMarks = $markResult['sequenceMarks'];

                if ($mark) {
                    $isCourseValidated = !$this->validationBase || $mark >= $this->validationBase ? 'v' : 'nv';
                }

                $module = $studentCourseRegistration->getModule();

//                    $marksResult[] = ['studentCourseRegistration' => $studentCourseRegistration, 'mark' => $mark, 'base' => 20, 'eliminated' => false, 'validated' => $isCourseValidated, 'sequenceMarks' => $sequenceMarks,'classProgram'=>$classProgram];
                $coeff = $classProgram->getCoeff();
                $markPeriodCourseCalculated = $this->periodMarkGenerationCourseUtil->generatePeriodCourseCalculated($studentCourseRegistration, $mark,  $isCourseValidated, $sequenceMarks, $classProgram, null, $this->validationBase, $coeff);
                if ($module) $moduleMarkPeriodCoursesCalculated[$module->getId()][] = $markPeriodCourseCalculated;
                else $notModuleMarkPeriodCoursesCalculated[] = $markPeriodCourseCalculated;

//                    $markPeriodCoursesCalculated[] = $markPeriodCourseCalculated;
            }
        }*/
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
                $markPeriodCoursesCalculated[] = $this->markSequenceGeneralAverageCalculatedRepository->findOneBy(['sequence' => $sequence, 'student' => $student, 'evaluationPeriod' => $evaluationPeriod]);
            }
        }

        $marksResult = [
            // Toutes les moyennes generales
            // Je renvoie ici car la logique est deja definie par rapport a un tableau de notes calculees de matiere (cf formule 1)
            'markPeriodCoursesCalculated' => $markPeriodCoursesCalculated,

            // Notes dans les modules
            'moduleMarkPeriodCoursesCalculated' => $moduleMarkPeriodCoursesCalculated,

            // Notes sans les modules
            'notModuleMarkPeriodCoursesCalculated' => $notModuleMarkPeriodCoursesCalculated
        ];
        return $marksResult;
    }

    // Calculer la note d'une sequence des matieres en considerant les type de notes
    // halfYearAverageFormula = 3
    function calculateAnnualCourseMarks3(array $studentCourseRegistrations): array
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

        $markPeriodCoursesCalculated = [];
        $moduleMarkPeriodCoursesCalculated = [];
        $notModuleMarkPeriodCoursesCalculated = [];

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
                    $markPeriodCourseCalculated = $this->sequenceMarkGenerationStudentCourseUtil->generatePeriodCourseCalculatedElimination($studentCourseRegistration,$mark,20,$isCourseValidated,$isEliminated,$sequenceMarks,$classProgram,$this->eliminateMark, $validationBase, $coeff);
                    $markPeriodCoursesCalculated[] = $markPeriodCourseCalculated;
                }
            }
        } else {*/
        /*foreach ($studentCourseRegistrations as $studentCourseRegistration) {
            $markExists = $this->annualMarkCalculationCourseUtil->markExists($studentCourseRegistration);
            if ($markExists) {
                $markResult = $this->calculatePeriodCourseMark($studentCourseRegistration);
                $mark = $markResult['mark'];
                $isCourseValidated = false;
                $classProgram = $studentCourseRegistration->getClassProgram();
                $sequenceMarks = $markResult['sequenceMarks'];

                if ($mark) {
                    $isCourseValidated = !$this->validationBase || $mark >= $this->validationBase ? 'v' : 'nv';
                }

                $module = $studentCourseRegistration->getModule();

//                    $marksResult[] = ['studentCourseRegistration' => $studentCourseRegistration, 'mark' => $mark, 'base' => 20, 'eliminated' => false, 'validated' => $isCourseValidated, 'sequenceMarks' => $sequenceMarks,'classProgram'=>$classProgram];
                $coeff = $classProgram->getCoeff();
                $markPeriodCourseCalculated = $this->periodMarkGenerationCourseUtil->generatePeriodCourseCalculated($studentCourseRegistration, $mark,  $isCourseValidated, $sequenceMarks, $classProgram, null, $this->validationBase, $coeff);
                if ($module) $moduleMarkPeriodCoursesCalculated[$module->getId()][] = $markPeriodCourseCalculated;
                else $notModuleMarkPeriodCoursesCalculated[] = $markPeriodCourseCalculated;

//                    $markPeriodCoursesCalculated[] = $markPeriodCourseCalculated;
            }
        }*/
//        }
        $student = $studentCourseRegistrations[0]->getStudRegistration();

        // Dans ce cas si, je redefenit le tableau des notes calculees des matieres des periodes car je calculais ensuite je generais directement
        // C'est pour cela qu'on peut voir que je renvoie le tableau des notes calculees des matieres avec les moyennes generales mais ca calcule toujours la moyennes generale du semestre
        // J'ecris ce commentaire ici car moi meme ca me fait lap car je suis en train de reflechir comment est ce que le code marche alors que moi meme je ne comprends pas ou est ce que je renvoie les moyennes generales des sequences pour le calcul de la moyenne generale de la periode
        // M.Herve avait raison j aime vraiment beaucoup les codes troooop compliques , le code c'est la magie. ;)
        // 20/05/2024 a 17:06

        foreach ($this->evaluationPeriodsArray as $evaluationPeriodId=> $sequences) {
            $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);
            $markPeriodCoursesCalculated[] = $this->markPeriodGeneralAverageCalculatedRepository->findOneBy(['student' => $student, 'evaluationPeriod' => $evaluationPeriod]);
        }

        $marksResult = [
            // Toutes les moyennes generales
            // Je renvoie ici car la logique est deja definie par rapport a un tableau de notes calculees de matiere (cf formule 1)
            'markPeriodCoursesCalculated' => $markPeriodCoursesCalculated,

            // Notes dans les modules
            'moduleMarkPeriodCoursesCalculated' => $moduleMarkPeriodCoursesCalculated,

            // Notes sans les modules
            'notModuleMarkPeriodCoursesCalculated' => $notModuleMarkPeriodCoursesCalculated
        ];
        return $marksResult;
    }
}