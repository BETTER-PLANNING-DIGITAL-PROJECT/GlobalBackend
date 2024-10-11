<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\All;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Course\PrimaryAndSecondary\AnnualMarkCalculationCoursesPrimaryAndSecondaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Course\Ternary\AnnualMarkCalculationCoursesTernaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\GeneralAverage\AnnualMarkCalculationGeneralAverageTernaryUtil;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCalculatedRepository;
use Doctrine\ORM\EntityManagerInterface;

// Classe contenant toutes les calculs et generations necessaires pour un etudiant pour une sequence
class AnnualMarkCalculationAllTernaryUtil
{
// Configurations
//    private bool $isEliminationCourseActivated = false;

    public function __construct(
        // Configurations
//        private ?float $eliminateMark,
        private readonly MarkAnnualGeneralAverageCalculatedRepository                                                  $markAnnualGeneralAverageCalculatedRepository,
        // Utils
        private readonly AnnualMarkCalculationCoursesTernaryUtil | AnnualMarkCalculationCoursesPrimaryAndSecondaryUtil $annualMarkCalculationCoursesUtil,
        private readonly AnnualMarkCalculationGeneralAverageTernaryUtil                                                $annualMarkCalculationGeneralAverageUtil,
        private readonly EntityManagerInterface                                                                        $entityManager
    )
    {
//        $this->isEliminationCourseActivated = $this->periodMarkCalculationUtil->isEliminationCourseActivated();
    }

    public function calculateAnnualAllMarks(string $case,mixed $element)
    {
        $this->{'calculateAnnual'.$case.'AllMarks'}($element);
    }

    // Calcul de toutes les notes pour un etudiant
    function calculateAnnualStudentAllMarks(StudentRegistration $student)
    {
        $markAnnualGeneralAverageCalculated = $this->markAnnualGeneralAverageCalculatedRepository->findOneBy(['student' => $student]);
        if (isset($markAnnualGeneralAverageCalculated)) {
            return false;
        }

        $studentCourseRegistrations = $this->annualMarkCalculationCoursesUtil->getCourses($student);
        if (!empty($studentCourseRegistrations)) {
            // Changement de l'etudiant a chaque iteration
            $annualMarkGenerationGeneralAverageUtil = $this->annualMarkCalculationGeneralAverageUtil->getAnnualMarkGenerationGeneralAverageUtil();
            $annualMarkGenerationUtil = $annualMarkGenerationGeneralAverageUtil->getAnnualMarkGenerationUtil();
            $annualMarkGenerationUtil->setStudent($student);

            $annualMarkGenerationGeneralAverageUtil->setAnnualMarkGenerationUtil($annualMarkGenerationUtil);// Calcul et generation de toutes les notes

            // Notes des matieres
            $marksResult = $this->annualMarkCalculationCoursesUtil->calculateAnnualCourseMarks($studentCourseRegistrations);//        dd($marksResult);
//            $moduleMarkPeriodCoursesCalculated = $marksResult['moduleMarkPeriodCoursesCalculated'];// Notes des modules
            //        dd($marksResult);

            /*if (count($moduleMarkPeriodCoursesCalculated) !== 0) {
                $this->periodMarkCalculationModulesUtil->calculateAnnualModuleMarks($moduleMarkPeriodCoursesCalculated);
            }*/

            $markCalculateds = $marksResult['markPeriodCoursesCalculated'];
            if (!empty($markCalculateds)) $this->annualMarkCalculationGeneralAverageUtil->calculateAnnualGeneralAverage($markCalculateds, $studentCourseRegistrations);
            $this->entityManager->flush();
        }
        return true;
    }

    // Calcul des notes pour plusieurs etudiants
    public function calculateAnnualStudentsAllMarks(array $students)
    {
        foreach ($students as $student) {
            $this->calculateAnnualStudentAllMarks($student);
        }
    }
}