<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\All;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Course\PrimaryAndSecondary\AnnualMarkCalculationCoursesPrimaryAndSecondaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Course\Ternary\AnnualMarkCalculationCoursesTernaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\GeneralAverage\AnnualMarkCalculationGeneralAveragePrimaryAndSecondaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\GeneralAverage\AnnualMarkCalculationGeneralAverageTernaryUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Module\PrimaryAndSecondary\AnnualMarkCalculationModulesPrimaryAndSecondaryUtil;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCalculatedRepository;
use Doctrine\ORM\EntityManagerInterface;

// Classe contenant toutes les calculs et generations necessaires pour un etudiant pour une sequence
class AnnualMarkCalculationAllPrimaryAndSecondaryUtil
{
// Configurations
//    private bool $isEliminationCourseActivated = false;

    public function __construct(
        // Configurations
//        private ?float $eliminateMark,
        private readonly MarkAnnualGeneralAverageCalculatedRepository                                                  $markAnnualGeneralAverageCalculatedRepository,
        // Utils
        private readonly AnnualMarkCalculationCoursesTernaryUtil | AnnualMarkCalculationCoursesPrimaryAndSecondaryUtil $annualMarkCalculationCoursesUtil,
        private readonly AnnualMarkCalculationModulesPrimaryAndSecondaryUtil                                           $annualMarkCalculationModulesUtil,
        private readonly AnnualMarkCalculationGeneralAveragePrimaryAndSecondaryUtil                                                $annualMarkCalculationGeneralAverageUtil,
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

            $annualMarkGenerationCourseUtil = $this->annualMarkCalculationCoursesUtil->getAnnualMarkGenerationCourseUtil();
            $annualMarkGenerationCourseUtil->setAnnualMarkGenerationUtil($annualMarkGenerationUtil);

            $annualMarkGenerationModuleUtil = $this->annualMarkCalculationModulesUtil->getAnnualMarkGenerationModuleUtil();
            $annualMarkGenerationModuleUtil->setAnnualMarkGenerationUtil($annualMarkGenerationUtil);

            // Notes des matieres
            $marksResult = $this->annualMarkCalculationCoursesUtil->calculateAnnualCourseMarks($studentCourseRegistrations);
            //        dd($marksResult);

            // Notes des modules
            $moduleMarkAnnualCoursesCalculated = $marksResult['moduleMarkAnnualCoursesCalculated'];
            //        dd($marksResult);

            if (count($moduleMarkAnnualCoursesCalculated) !== 0) {
                $this->annualMarkCalculationModulesUtil->calculateAnnualModuleMarks($moduleMarkAnnualCoursesCalculated);
            }

            // Calcul des moyennes generales
            $markCalculateds = $marksResult['markAnnualCoursesCalculated'];
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