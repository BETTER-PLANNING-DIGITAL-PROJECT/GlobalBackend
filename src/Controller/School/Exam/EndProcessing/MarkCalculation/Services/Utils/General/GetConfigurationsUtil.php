<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General;
use App\Entity\School\Exam\Configuration\ClassWeighting;
use App\Entity\School\Exam\Configuration\ExamInstitutionSettings;
use App\Entity\School\Exam\Configuration\FormulaTh;
use App\Entity\School\Exam\Configuration\MarkGrade;
use App\Entity\School\Exam\Configuration\SchoolWeighting;
use App\Entity\School\Exam\Configuration\SpecialityWeighting;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\Session\Year;
use App\Repository\School\Exam\Configuration\ClassWeightingRepository;
use App\Repository\School\Exam\Configuration\ExamInstitutionSettingsRepository;
use App\Repository\School\Exam\Configuration\FormulaThRepository;
use App\Repository\School\Exam\Configuration\MarkGradeRepository;
use App\Repository\School\Exam\Configuration\SchoolWeightingRepository;
use App\Repository\School\Exam\Configuration\SpecialityWeightingRepository;
use Doctrine\ORM\NonUniqueResultException;

// Recuperer les parametres permettant les calculs de notes
class GetConfigurationsUtil
{

    private ?FormulaTh $formulaTh;
    private ?ExamInstitutionSettings $examInstitutionSettings;

    public function __construct(
        // Attributs
        private readonly Year $year,

        // Repository
        private readonly FormulaThRepository $formulaThRepository,
        private readonly ExamInstitutionSettingsRepository $examInstitutionSettingsRepository,
        private readonly SchoolWeightingRepository $schoolWeightingRepository,
        private readonly ClassWeightingRepository $classWeightingRepository,
        private readonly SpecialityWeightingRepository $specialityWeightingRepository,
        private readonly MarkGradeRepository $markGradeRepository,
    )
    {
      $this->formulaTh = $this->formulaThRepository->findOneBy(['year' => $this->year]);
      $this->examInstitutionSettings = $this->examInstitutionSettingsRepository->findOneBy(['institution' => $this->year->getInstitution()]);
    }

    // Recuperer la ponderation de plus grande priorite
    public function getMaxWeighting(?SchoolClass $class): mixed
    {
        $school = $class->getSchool();
        $year = $class->getYear();
        $speciality = $class->getSpeciality();

        $schoolWeighting = $this->schoolWeightingRepository->findOneBy(['year' => $year, 'school' => $school]);
        $classWeighting = $this->classWeightingRepository->findOneBy(['year' => $year, 'class' => $class]);
        $specialityWeighting = $this->specialityWeightingRepository->findOneBy(['year' => $year, 'speciality' => $speciality]);
        $weightings = array($schoolWeighting, $classWeighting, $specialityWeighting);

        // Retirer ce qui est null
        $weightings = array_values(array_filter($weightings, function (mixed $weighting) {
            return isset($weighting);
        }));

        $count = count($weightings);
        if ($count === 1) return $weightings[0];
        else if ($count > 1) {
            $weightingsArray = array_map(function (SchoolWeighting|SpecialityWeighting|ClassWeighting $weighting) {
                return
                    [
                        'weighting' => $weighting,
                        'classPriority' => ($weighting instanceof ClassWeighting) ? 3 : (($weighting instanceof SpecialityWeighting) ? 2 : 1),
                        'priority' => $weighting->getPriority()
                    ];
            }, $weightings);
            array_multisort(array_column($weightingsArray, 'priority'), SORT_DESC, array_column($weightingsArray, 'classPriority'), SORT_DESC, $weightingsArray);
            return $weightingsArray[0]['weighting'];
        }
        return null;
    }

    // Recuperer la consideration des coefficients nulles de la ponderation correspondante a l'etudiant
    public function isIsCoefficientOfNullMarkConsiderInTheAverageCalculation(SchoolClass $class): bool
    {
        $maxWeighting = $this->getMaxWeighting($class);
        $isCoefficientOfNullMarkConsiderInTheAverageCalculation = isset($maxWeighting) && $maxWeighting->isIsCoefficientOfNullMarkConsiderInTheAverageCalculation();
        return $isCoefficientOfNullMarkConsiderInTheAverageCalculation;
    }

    public function getFormulaTh(): ?FormulaTh
    {
        return $this->formulaTh;
    }

    public function setFormulaTh(?FormulaTh $formulaTh): GetConfigurationsUtil
    {
        $this->formulaTh = $formulaTh;
        return $this;
    }

    public function getExamInstitutionSettings(): ?ExamInstitutionSettings
    {
        return $this->examInstitutionSettings;
    }

    public function setExamInstitutionSettings(?ExamInstitutionSettings $examInstitutionSettings): GetConfigurationsUtil
    {
        $this->examInstitutionSettings = $examInstitutionSettings;
        return $this;
    }

    // Recuperer le mark grade associee a une note
    function getMarkGrade(School $school, ?float $mark) : ?MarkGrade
    {
        if (!isset($mark)) return null;
        try {
            return $this->markGradeRepository->getBySchoolForMark($school, $mark);
        } catch (NonUniqueResultException) {
            return null;
        }
    }
}