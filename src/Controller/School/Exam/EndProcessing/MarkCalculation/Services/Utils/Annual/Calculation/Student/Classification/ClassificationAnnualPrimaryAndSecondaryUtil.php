<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student\Classification;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\ClassificationUtil;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Repository\School\Exam\Operation\Annual\Course\MarkAnnualCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculatedRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;

class ClassificationAnnualPrimaryAndSecondaryUtil extends ClassificationUtil
{

    public function __construct(
        // Configurations
        bool $calculateAveragesForUnclassified,
        float $percentageSubjectNumber,
        string $andOr,
        float $percentageTotalCoefficicient,

        private array $evaluationPeriods,

        // Repository
        protected readonly StudentCourseRegistrationRepository $studentCourseRegistrationRepository,
        protected readonly MarkPeriodCourseCalculatedRepository $markPeriodCourseCalculatedRepository,
    )
    {
        parent::__construct($calculateAveragesForUnclassified, $percentageSubjectNumber, $andOr, $percentageTotalCoefficicient);
    }

    function isComposed(StudentCourseRegistration $studentCourseRegistration): bool
    {
        // On considere une matiere composee pour une annee si les notes des periodes de la matiere sont presentes et sont calculees (cad entre 0 et 20)
        $student = $studentCourseRegistration->getStudRegistration();
        $classProgram = $studentCourseRegistration->getClassProgram();
        $codeuvc = $classProgram->getCodeuvc();
        $nameuvc = $classProgram->getNameuvc();

        // Recuperations des autres inscriptions aux matieres pour l'etudiant pour les autres semestres
        $studentCourseRegistrations = array_map(fn(EvaluationPeriod $evaluationPeriod)=>array($evaluationPeriod->getId() =>$this->studentCourseRegistrationRepository->findByStudentOtherEvaluationPeriodRegistration($studentCourseRegistration, $evaluationPeriod)),$this->evaluationPeriods);

        $studentCourseRegistrations = array_merge(...$studentCourseRegistrations);

        $markPeriodCourseCalculateds = array_map(fn(?StudentCourseRegistration $studentCourseRegistration)=>$this->markPeriodCourseCalculatedRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration,'evaluationPeriod'=>$studentCourseRegistration?->getEvaluationPeriod()]),$studentCourseRegistrations);

        if(empty($markPeriodCourseCalculateds)) return false;

        foreach ($markPeriodCourseCalculateds as $markPeriodCourseCalculated) {
            if (!$markPeriodCourseCalculated) return false;
            $mark = $markPeriodCourseCalculated->getMark();
            if (!isset($mark)) return false;
        }

        return true;
    }

}