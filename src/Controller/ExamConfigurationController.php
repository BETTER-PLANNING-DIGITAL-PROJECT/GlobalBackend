<?php

namespace App\Controller;

use App\Entity\School\Exam\Configuration\ClassWeighting;
use App\Entity\School\Exam\Configuration\CycleWeighting;
use App\Entity\School\Exam\Configuration\FormulaTh;
use App\Entity\School\Exam\Configuration\GraduationConditions;
use App\Entity\School\Exam\Configuration\PromotionConditions;
use App\Entity\School\Exam\Configuration\SchoolWeighting;
use App\Entity\School\Exam\Configuration\SpecialityWeighting;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\ClassWeightingRepository;
use App\Repository\School\Exam\Configuration\CycleWeightingRepository;
use App\Repository\School\Exam\Configuration\ExamInstitutionSettingsRepository;
use App\Repository\School\Exam\Configuration\FormulaThRepository;
use App\Repository\School\Exam\Configuration\GraduationConditionsRepository;
use App\Repository\School\Exam\Configuration\PromotionConditionsRepository;
use App\Repository\School\Exam\Configuration\SchoolWeightingRepository;
use App\Repository\School\Exam\Configuration\SpecialityWeightingRepository;
use App\Repository\School\Schooling\Configuration\CycleRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Configuration\SpecialityRepository;
use App\Repository\Setting\School\PeriodTypeRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ExamConfigurationController extends AbstractController
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly EntityManagerInterface $entityManager,
        private readonly FormulaThRepository $formulaThRepository,
        private readonly SchoolRepository $schoolRepository,
        private readonly SchoolWeightingRepository $schoolWeightingRepository,
        private readonly CycleRepository $cycleRepository,
        private readonly CycleWeightingRepository $cycleWeightingRepository,
        private readonly SpecialityRepository $specialityRepository,
        private readonly SpecialityWeightingRepository $specialityWeightingRepository,
        private readonly SchoolClassRepository $classRepository,
        private readonly ClassWeightingRepository $classWeightingRepository
    )
    {
    }

    public function getUser(): ?User
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }

    // Formula Th
    #[Route('api/formula-th/create/{formulaThId}', name: 'formula_th_generate')]
    public function generateFormulaTh(string $formulaThId = null): JSONResponse
    {
        $user = $this->getUser();
        $currentYear = $user->getCurrentYear();
        $institution = $user->getInstitution();
        $formulaTh = $this->formulaThRepository->findOneBy(['year'=>$currentYear]);
        if ($formulaTh !== null){
            return $this->json('exists');
        }
        $formulaTh = new FormulaTh();
        $formulaTh->setUser($user);
        $formulaTh->setInstitution($institution);
        $formulaTh->setYear($currentYear);

        if (isset($formulaThId)) {
            $formulaThModel = $this->formulaThRepository->find($formulaThId);
            $formulaTh->setHalfYearAverageFormula($formulaThModel->getHalfYearAverageFormula());
            $formulaTh->setFinalAverageFormula($formulaThModel->getFinalAverageFormula());
            $formulaTh->setWarningConductAbsenceHours($formulaThModel->getWarningConductAbsenceHours());
            $formulaTh->setWarningConductExclusionDays($formulaThModel->getWarningConductExclusionDays());
            $formulaTh->setBlameConductAbsenceHours($formulaThModel->getBlameConductAbsenceHours());
            $formulaTh->setBlameConductExclusionDays($formulaThModel->getBlameConductExclusionDays());
            $formulaTh->setBlameWorkAverage($formulaThModel->getBlameWorkAverage());
            $formulaTh->setWarningWorkAverage($formulaThModel->getWarningWorkAverage());
            $formulaTh->setGrantThCompositionAverage($formulaThModel->getGrantThCompositionAverage());
            $formulaTh->setGrantThAnnualAverage($formulaThModel->getGrantThAnnualAverage());
            $formulaTh->setGrantEncouragementAverage($formulaThModel->getGrantEncouragementAverage());
            $formulaTh->setGrantCongratulationAverage($formulaThModel->getGrantCongratulationAverage());
            $formulaTh->setRefuseThAbsenceHours($formulaThModel->getRefuseThAbsenceHours());
            $formulaTh->setRefuseThExclusionDays($formulaThModel->getRefuseThExclusionDays());
            $formulaTh->setRefuseThSetHours($formulaThModel->getRefuseThSetHours());
            $formulaTh->setRefuseThNumberOfBlame($formulaThModel->getRefuseThNumberOfBlame());
            $formulaTh->setPercentageSubjectNumber($formulaThModel->getPercentageSubjectNumber());
            $formulaTh->setAndOr($formulaThModel->getAndOr());
            $formulaTh->setPercentageTotalCoefficicient($formulaThModel->getPercentageTotalCoefficicient());
        }

        $this->entityManager->persist($formulaTh);
        $this->entityManager->flush();
        return $this->json('ok');
    }

    // Exam Institution Settings
    #[Route('/api/exam-institution-settings/get-item', name: 'exam_institution_settings_get_item')]
    public function getExamInstitutionSettings(ExamInstitutionSettingsRepository $examInstitutionSettingsRepository): JsonResponse
    {
        $user = $this->getUser();
        $institution = $user->getInstitution();
        $year = $user->getCurrentYear();
        $examInstitutionSettings = $examInstitutionSettingsRepository->findOneBy(['institution'=>$institution,'year'=>$year]);
        return $this->json($examInstitutionSettings);
    }

    // Ponderations
    #[Route('api/school/weighting/create/{schoolId}/{schoolWeightingId}', name: 'school_weighting_generate')]
    public function generateSchoolWeigthing(string $schoolId,string $schoolWeightingId = null): JSONResponse
    {
        $user = $this->getUser();
        $currentYear = $user->getCurrentYear();
        $institution = $user->getInstitution();
        $school = $this->schoolRepository->find($schoolId);
        $schoolWeighting = $this->schoolWeightingRepository->findOneBy(['school'=>$school,'year'=>$currentYear]);
        if ($schoolWeighting !== null){
            return $this->json('exists');
        }
        $schoolWeighting = new SchoolWeighting();
        $schoolWeighting->setUser($user);
        $schoolWeighting->setSchool($school);
        $schoolWeighting->setInstitution($institution);
        $schoolWeighting->setYear($currentYear);

        if (isset($schoolWeightingId)) {
            $schoolWeightingModel = $this->schoolWeightingRepository->find($schoolWeightingId);
            $this->setWeighting($schoolWeighting, $schoolWeightingModel);
        }

        $this->entityManager->persist($schoolWeighting);
        $this->entityManager->flush();
        return $this->json('ok');
    }

    #[Route('api/cycle/weighting/create/{cycleId}/{cycleWeightingId}', name: 'cycle_weighting_generate')]
    public function generateCycleWeigthing(string $cycleId,string $cycleWeightingId = null): JSONResponse
    {
        $user = $this->getUser();
        $currentYear = $user->getCurrentYear();
        $institution = $user->getInstitution();
        $cycle = $this->cycleRepository->find($cycleId);
        $cycleWeighting = $this->cycleWeightingRepository->findOneBy(['cycle'=>$cycle,'year'=>$currentYear]);
        if ($cycleWeighting !== null){
            return $this->json('exists');
        }
        $cycleWeighting = new CycleWeighting();
        $cycleWeighting->setUser($user);
        $cycleWeighting->setCycle($cycle);
        $cycleWeighting->setInstitution($institution);
        $cycleWeighting->setYear($currentYear);

        if (isset($cycleWeightingId)) {
            $cycleWeightingModel = $this->cycleWeightingRepository->find($cycleWeightingId);
            $this->setWeighting($cycleWeighting, $cycleWeightingModel);
        }

        $this->entityManager->persist($cycleWeighting);
        $this->entityManager->flush();
        return $this->json('ok');
    }

    #[Route('api/speciality/weighting/create/{specialityId}/{specialityWeightingId}', name: 'speciality_weighting_generate')]
    public function generateSpecialityWeigthing(string $specialityId,string $specialityWeightingId = null): JSONResponse
    {
        $user = $this->getUser();
        $currentYear = $user->getCurrentYear();
        $institution = $user->getInstitution();
        $speciality = $this->specialityRepository->find($specialityId);
        $specialityWeighting = $this->specialityWeightingRepository->findOneBy(['speciality'=>$speciality,'year'=>$currentYear]);
        if ($specialityWeighting !== null){
            return $this->json('exists');
        }
        $specialityWeighting = new SpecialityWeighting();
        $specialityWeighting->setUser($user);
        $specialityWeighting->setSpeciality($speciality);
        $specialityWeighting->setInstitution($institution);
        $specialityWeighting->setYear($currentYear);

        if (isset($specialityWeightingId)) {
            $specialityWeightingModel = $this->specialityWeightingRepository->find($specialityWeightingId);
            $this->setWeighting($specialityWeighting, $specialityWeightingModel);
        }

        $this->entityManager->persist($specialityWeighting);
        $this->entityManager->flush();
        return $this->json('ok');
    }

    #[Route('api/class/weighting/create/{classId}/{classWeightingId}', name: 'class_weighting_generate')]
    public function generateClassWeigthing(string $classId,string $classWeightingId = null): JSONResponse
    {
        $user = $this->getUser();
        $currentYear = $user->getCurrentYear();
        $institution = $user->getInstitution();
        $class = $this->classRepository->find($classId);
        $classWeighting = $this->classWeightingRepository->findOneBy(['class'=>$class,'year'=>$currentYear]);
        if ($classWeighting !== null){
            return $this->json('exists');
        }
        $classWeighting = new ClassWeighting();
        $classWeighting->setUser($user);
        $classWeighting->setSchool($class->getSchool());
        $classWeighting->setClass($class);
        $classWeighting->setInstitution($institution);
        $classWeighting->setYear($currentYear);

        if (isset($classWeightingId)) {
            $classWeightingModel = $this->classWeightingRepository->find($classWeightingId);
            $this->setWeighting($classWeighting, $classWeightingModel);
        }

        $this->entityManager->persist($classWeighting);
        $this->entityManager->flush();
        return $this->json('ok');
    }

    /**
     * @param SpecialityWeighting | CycleWeighting | SchoolWeighting | ClassWeighting $weighting
     * @param SpecialityWeighting | CycleWeighting | SchoolWeighting | ClassWeighting | null $weightingModel
     * @return void
     */
    public function setWeighting(SpecialityWeighting | CycleWeighting | SchoolWeighting | ClassWeighting $weighting, SpecialityWeighting | CycleWeighting | SchoolWeighting | ClassWeighting | null $weightingModel): void
    {
        $weighting->setEliminateMark($weightingModel->getEliminateMark());
        $weighting->setEntryBase($weightingModel->getEntryBase());
        $weighting->setGeneralEliminateAverage($weightingModel->getGeneralEliminateAverage());
        $weighting->setIsCoefficientOfNullMarkConsiderInTheAverageCalculation($weightingModel->isIsCoefficientOfNullMarkConsiderInTheAverageCalculation());
        $weighting->setIsMarkForAllSequenceRequired($weightingModel->isIsMarkForAllSequenceRequired());
        $weighting->setIsValidateCompensateModulate($weightingModel->isIsValidateCompensateModulate());
        $weighting->setNumberOfDivision($weightingModel->getNumberOfDivision());
        $weighting->setPeriodType($weightingModel->getPeriodType());
        $weighting->setValidationMark($weightingModel->getValidationMark());
    }
}
