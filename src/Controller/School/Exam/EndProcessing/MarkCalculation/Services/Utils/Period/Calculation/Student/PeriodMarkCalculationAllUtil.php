<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Calculation\Student;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Calculation\Student\Course\PeriodMarkCalculationCoursesUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Calculation\Student\GeneralAverage\PeriodMarkCalculationGeneralAverageUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Calculation\Student\Module\PeriodMarkCalculationModulesUtil;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculatedRepository;
use Doctrine\ORM\EntityManagerInterface;

// Classe contenant toutes les calculs et generations necessaires pour un etudiant pour une sequence
class PeriodMarkCalculationAllUtil
{
// Configurations
//    private bool $isEliminationCourseActivated = false;


    public function __construct(
        // Configurations
//        private ?float $eliminateMark,
        private readonly EvaluationPeriod                                    $evaluationPeriod,
        private readonly MarkPeriodCourseCalculatedRepository                $markPeriodCourseCalculatedRepository,

        // Utils
        private readonly PeriodMarkCalculationCoursesUtil                    $periodMarkCalculationCoursesUtil,
        private readonly PeriodMarkCalculationModulesUtil                    $periodMarkCalculationModulesUtil,
        private readonly PeriodMarkCalculationGeneralAverageUtil             $periodMarkCalculationGeneralAverageUtil,
        private readonly EntityManagerInterface                              $entityManager
    )
    {
//        $this->isEliminationCourseActivated = $this->periodMarkCalculationUtil->isEliminationCourseActivated();
    }

    public function calculatePeriodAllMarks(string $case,mixed $element)
    {
        $this->{'calculatePeriod'.$case.'AllMarks'}($element);
    }

    // Calcul de toutes les notes pour un etudiant
    function calculatePeriodStudentAllMarks(StudentRegistration $student)
    {
        if ($this->markPeriodCourseCalculatedRepository->findOneBy(['evaluationPeriod' => $this->evaluationPeriod, 'student' => $student])) {
            return false;
        }

        $studentCourseRegistrations = $this->periodMarkCalculationCoursesUtil->getCourses($student);

        if (!empty($studentCourseRegistrations)) {
            // Changement de l'etudiant a chaque iteration
            $periodMarkGenerationCourseUtil = $this->periodMarkCalculationCoursesUtil->getPeriodMarkGenerationCourseUtil();
            $periodMarkGenerationUtil = $periodMarkGenerationCourseUtil->getPeriodMarkGenerationUtil();
            $periodMarkGenerationUtil->setStudent($student);
            $periodMarkGenerationCourseUtil->setPeriodMarkGenerationUtil($periodMarkGenerationUtil);
            $periodMarkGenerationModuleUtil = $this->periodMarkCalculationModulesUtil->getPeriodMarkGenerationModuleUtil();
            $periodMarkGenerationModuleUtil->setPeriodMarkGenerationUtil($periodMarkGenerationUtil);
            $periodMarkGenerationGeneralAverageUtil = $this->periodMarkCalculationGeneralAverageUtil->getPeriodMarkGenerationGeneralAverageUtil();
            $periodMarkGenerationGeneralAverageUtil->setPeriodMarkGenerationUtil($periodMarkGenerationUtil);// Calcul et generation de toutes les notes

            // Notes des matieres
            $marksResult = $this->periodMarkCalculationCoursesUtil->calculatePeriodCourseMarks($studentCourseRegistrations);//        dd($marksResult);
            $moduleMarkPeriodCoursesCalculated = $marksResult['moduleMarkPeriodCoursesCalculated'];// Notes des modules
            //        dd($marksResult);

            if (count($moduleMarkPeriodCoursesCalculated) !== 0) {
                $this->periodMarkCalculationModulesUtil->calculatePeriodModuleMarks($moduleMarkPeriodCoursesCalculated);
            }

            $markPeriodCoursesCalculated = $marksResult['markPeriodCoursesCalculated'];
            if (!empty($markPeriodCoursesCalculated)) $this->periodMarkCalculationGeneralAverageUtil->calculatePeriodGeneralAverage($markPeriodCoursesCalculated, $studentCourseRegistrations);
            $this->entityManager->flush();
        }
        return true;
    }

    // Calcul des notes pour plusieurs etudiants
    public function calculatePeriodStudentsAllMarks(array $students)
    {
        foreach ($students as $student) {
            $this->calculatePeriodStudentAllMarks($student);
        }
    }
}