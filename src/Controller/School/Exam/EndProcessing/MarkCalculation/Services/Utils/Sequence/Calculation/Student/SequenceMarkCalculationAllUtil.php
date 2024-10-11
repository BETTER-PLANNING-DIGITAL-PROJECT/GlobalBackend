<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\Course\SequenceMarkCalculationCoursesUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\Course\SequenceMarkCalculationCourseUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\GeneralAverage\SequenceMarkCalculationGeneralAverageUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Calculation\Student\Module\SequenceMarkCalculationModulesUtil;
use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Sequence\Generation\Student\Course\SequenceMarkGenerationCourseUtil;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;

// Classe contenant toutes les calculs et generations necessaires pour un etudiant pour une sequence
class SequenceMarkCalculationAllUtil
{
// Configurations
//    private bool $isEliminationCourseActivated = false;


    public function __construct(
        // Configurations
//        private ?float $eliminateMark,
        private EvaluationPeriod $evaluationPeriod,
        private Sequence $sequence,
        private readonly MarkSequenceCourseCalculatedRepository $markSequenceCourseCalculatedRepository,
        // Utils
        private SequenceMarkCalculationCoursesUtil        $sequenceMarkCalculationCoursesUtil,
        private SequenceMarkCalculationModulesUtil        $sequenceMarkCalculationModulesUtil,
        private SequenceMarkCalculationGeneralAverageUtil $sequenceMarkCalculationGeneralAverageUtil,
        private readonly EntityManagerInterface $entityManager
    )
    {
//        $this->isEliminationCourseActivated = $this->sequenceMarkCalculationUtil->isEliminationCourseActivated();
    }

    public function calculateSequenceAllMarks(string $case,mixed $element)
    {
        $this->{'calculateSequence'.$case.'AllMarks'}($element);
    }

    // Calcul de toutes les notes pour un etudiant
    function calculateSequenceStudentAllMarks(StudentRegistration $student)
    {
        if ($this->markSequenceCourseCalculatedRepository->findOneBy(['evaluationPeriod' => $this->evaluationPeriod,'sequence'=>$this->sequence, 'student' => $student])) {
            return false;
        }

        // Changement de l'etudiant a chaque iteration
        $sequenceMarkGenerationCourseUtil = $this->sequenceMarkCalculationCoursesUtil->getSequenceMarkGenerationStudentCourseUtil();
        $sequenceMarkGenerationUtil = $sequenceMarkGenerationCourseUtil->getSequenceMarkGenerationUtil();
        $sequenceMarkGenerationUtil->setStudent($student);
        $sequenceMarkGenerationCourseUtil->setSequenceMarkGenerationUtil($sequenceMarkGenerationUtil);

        $sequenceMarkGenerationModuleUtil = $this->sequenceMarkCalculationModulesUtil->getSequenceMarkGenerationStudentModuleUtil();
        $sequenceMarkGenerationModuleUtil->setSequenceMarkGenerationUtil($sequenceMarkGenerationUtil);

        $sequenceMarkGenerationGeneralAverageUtil = $this->sequenceMarkCalculationGeneralAverageUtil->getSequenceMarkGenerationStudentGeneralAverageUtil();
        $sequenceMarkGenerationGeneralAverageUtil->setSequenceMarkGenerationUtil($sequenceMarkGenerationUtil);

        // Calcul et generation de toutes les notes

        $studentCourseRegistrations = $this->sequenceMarkCalculationCoursesUtil->getCourses($student);
        // Notes des matieres
        $marksResult = $this->sequenceMarkCalculationCoursesUtil->calculateSequenceCourseMarks($studentCourseRegistrations);
//        dd($marksResult);
        $moduleMarkSequenceCoursesCalculated = $marksResult['moduleMarkSequenceCoursesCalculated'];

        // Notes des modules
//        dd($marksResult);
        if (count($moduleMarkSequenceCoursesCalculated) !== 0) {
            $this->sequenceMarkCalculationModulesUtil->calculateSequenceModuleMarks($moduleMarkSequenceCoursesCalculated);
        }

        $markSequenceCoursesCalculated = $marksResult['markSequenceCoursesCalculated'];
        $this->sequenceMarkCalculationGeneralAverageUtil->calculateSequenceGeneralAverage($markSequenceCoursesCalculated, $studentCourseRegistrations);
        $this->entityManager->flush();
        return true;
    }

    // Calcul des notes pour plusieurs etudiants
    public function calculateSequenceStudentsAllMarks(array $students)
    {
        foreach ($students as $student) {
            $this->calculateSequenceStudentAllMarks($student);
        }
    }
}