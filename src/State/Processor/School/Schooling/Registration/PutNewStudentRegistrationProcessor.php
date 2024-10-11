<?php
namespace App\State\Processor\School\Schooling\Registration;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Controller\StudentRegistrationController;
use App\Entity\Partner\Customer;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\Institution\InstitutionRepository;
use App\Repository\Security\Session\YearRepository;
use App\Repository\Security\SystemSettingsRepository;
use App\Repository\Setting\Location\CountryRepository;
use App\Repository\Setting\Person\BloodGroupRepository;
use App\Repository\Setting\Person\CivilityRepository;
use App\Repository\Setting\Person\ReligionRepository;
use App\Repository\Setting\Person\RhesusRepository;
use App\Repository\Setting\Person\SexRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PutNewStudentRegistrationProcessor implements ProcessorInterface
{
    private Request $request;
    private EntityManagerInterface $manager;
    private YearRepository $yearRepo;
    private CountryRepository $countryRepo;
    private SexRepository $sexRepo;
    private ReligionRepository $religionRepo;
    private BloodGroupRepository $bloodGroupRepo;
    private RhesusRepository $rhesusRepo;
    private CivilityRepository $civilityRepo;
    private InstitutionRepository $institutionRepo;
    private SchoolClassRepository $classRepo;
    private SchoolRepository $schoolRepo;
    private ClassProgramRepository $classProgramRepo;
    private  BranchRepository $branchRepository;
    private SystemSettingsRepository $systemSettingsRepository;
    private StudentRegistrationRepository $studRegistrationRepo;
    private StudentRegistrationController $studRegistrationController;


    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                Request $request, RhesusRepository $rhesusRepo,
                                EntityManagerInterface $manager,
                                CountryRepository $countryRepo,
                                BloodGroupRepository          $bloodGroupRepo,
                                StudentRepository $studentRepository,
                                SexRepository                 $sexRepo,
                                ReligionRepository            $religionRepo,
                                InstitutionRepository         $institutionRepo,
                                CivilityRepository            $civilityRepo,
                                SchoolClassRepository         $classRepo,
                                YearRepository                $yearRepo,
                                SchoolRepository              $schoolRepo,
                                BranchRepository $branchRepository,
                                SystemSettingsRepository $systemSettingsRepository,
                                ClassProgramRepository        $classProgramRepo,
                                StudentRegistrationRepository $studRegistrationRepo,
                                StudentRegistrationController $studRegistrationController,
                                private readonly StudentCourseRegistrationRepository $studentCourseRegistrationRepository
    ) {

        $this->req = $request;
        $this->manager = $manager;
        $this->yearRepo = $yearRepo;
        $this->countryRepo = $countryRepo;
        $this->sexRepo = $sexRepo;
        $this->religionRepo = $religionRepo;
        $this->bloodGroupRepo = $bloodGroupRepo;
        $this->studentRepository = $studentRepository;
        $this->rhesusRepo = $rhesusRepo;
        $this->civilityRepo = $civilityRepo;
        $this->institutionRepo = $institutionRepo;
        $this->classRepo = $classRepo;
        $this->schoolRepo = $schoolRepo;
        $this->classProgramRepo = $classProgramRepo;
        $this->studRegistrationRepo = $studRegistrationRepo;
        $this->studRegistrationController = $studRegistrationController;
        $this->branchRepository = $branchRepository;
        $this->systemSettingsRepository = $systemSettingsRepository;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $studentData = json_decode($this->req->getContent(), true);

        $matricule = $studentData['matricule'];
        $othermatricule = !isset($studentData['othermatricule']) ? null : $studentData['othermatricule'];
        $internalmatricule = !isset($studentData['internalmatricule']) ? null : $studentData['internalmatricule'];

        // Check if student matricule already exist
        $student = $this->studentRepository->findOneBy(['matricule' => $matricule]);
        if($student && ($student != $data->getStudent()))
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This matricule already exist.'], 400);
        }

        // Check if student other matricule already exist
        $student = $this->studentRepository->findOneBy(['othermatricule' => $othermatricule]);
        if($othermatricule != null && $student && ($student != $data->getStudent()))
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This ministry matricule already exist.'], 400);
        }

        // Check if student internal matricule already exist
        $student = $this->studentRepository->findOneBy(['internalmatricule' => $internalmatricule]);
        if($internalmatricule != null && $student && ($student != $data->getStudent()))
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This internal matricule already exist.'], 400);
        }


        $year = !isset($studentData['year']) ? null : $this->yearRepo->find($this->getIdFromApiResourceId($studentData['year']));

        if($year) $data->getStudent()->setYear($year);
        $data->getStudent()->setCountry(!isset($studentData['country']) ? null : $this->countryRepo->find($this->getIdFromApiResourceId($studentData['country'])));
        $data->getStudent()->setBloodGroup(!isset($studentData['bloodGroup']) ? null : $this->bloodGroupRepo->find($this->getIdFromApiResourceId($studentData['bloodGroup'])));
        $data->getStudent()->setRhesus(!isset($studentData['rhesus']) ? null : $this->rhesusRepo->find($this->getIdFromApiResourceId($studentData['rhesus'])));
        $data->getStudent()->setMatricule($studentData['matricule']);
        $data->getStudent()->setOtherMatricule($othermatricule);
        $data->getStudent()->setInternalmatricule($internalmatricule);
        $data->getStudent()->setName($studentData['name']);
        $data->getStudent()->setFirstName($studentData['firstName']);
        $new = new \DateTimeImmutable($studentData['dob']);
        $data->getStudent()->setDob($new);
        // $new = new \DateTimeImmutable($studentData['bornAround']);
        // $data->getStudent()->setBornAround($new);
        $data->getStudent()->setPob(!isset($studentData['pob']) ? null : ($studentData['pob']));
        $data->getStudent()->setSex(!isset($studentData['sex']) ? null : $this->sexRepo->find($this->getIdFromApiResourceId($studentData['sex'])));
        $data->getStudent()->setRegion(!isset($studentData['region']) ? "" : $studentData['region']);
        $data->getStudent()->setReligion(!isset($studentData['religion']) ? null : $this->religionRepo->find($this->getIdFromApiResourceId($studentData['religion'])));
        
        $data->getStudent()->setStutterer($studentData['stutterer']);
        $data->getStudent()->setLeftHanded($studentData['leftHanded']);
        $data->getStudent()->setHearingProblem($studentData['hearingProblem']);
        $data->getStudent()->setEyeProblem($studentData['eyeProblem']);
        /*$data->getStudent()->setVaccine($studentData['vaccine']);
        $data->getStudent()->setVaccineProhibited($studentData['vaccineProhibited']);*/
        $data->getStudent()->setMedicalHistory($studentData['medicalHistory']);

        if (isset($studentData['studentphone'])) $data->getStudent()->setStudentphone($studentData['studentphone']);
        if (isset($studentData['studentemail'])) $data->getStudent()->setStudentemail($studentData['studentemail']);
        $data->getStudent()->setStudentProfession($studentData['studentProfession']);
        $data->getStudent()->setStudentDistrict($studentData['studentDistrict']);
        $data->getStudent()->setStudentAddress($studentData['studentAddress']);
        $data->getStudent()->setStudentTown($studentData['studentTown']);
        $data->getStudent()->setFathername($studentData['fathername']);
        $data->getStudent()->setFatherphone($studentData['fatherphone']);
        $data->getStudent()->setFatheremail($studentData['fatheremail']);
        $data->getStudent()->setFatherprofession($studentData['fatherprofession']);
        $data->getStudent()->setMothername($studentData['mothername']);
        $data->getStudent()->setMotherphone($studentData['motherphone']);
        $data->getStudent()->setMotheremail($studentData['motheremail']);
        $data->getStudent()->setMotherprofession($studentData['motherprofession']);
        $data->getStudent()->setGuardianname($studentData['guardianname']);
        $data->getStudent()->setGuardianphone($studentData['guardianphone']);
        $data->getStudent()->setGuardianprofession($studentData['guardianprofession']);
        
        $data->getStudent()->setPartnerName($studentData['partnerName']);
        $data->getStudent()->setPartnerPhone($studentData['partnerPhone']);
        $data->getStudent()->setPartnerEmail($studentData['partnerEmail']);
        $data->getStudent()->setPartnerProfession($studentData['partnerProfession']);
        // $data->getStudent()->setCivility(!isset($studentData['civility']) ? null : $this->civilityRepo->find($this->getIdFromApiResourceId($studentData['civility'])));
        $numberOfChildren = intval($studentData['numberOfChildren']);
        $data->getStudent()->setNumberOfChildren($numberOfChildren);

        $data->setStudent($data->getStudent());


        $school = !isset($studentData['school']) ? null : $this->schoolRepo->find($this->getIdFromApiResourceId($studentData['school']));
//        $year = !isset($studentData['year']) ? null : $this->yearRepo->find($this->getIdFromApiResourceId($studentData['year']));
        $class = !isset($studentData['classe']) ? null : $this->classRepo->find($this->getIdFromApiResourceId($studentData['classe']));

        $data->setCurrentYear($year);
        $data->setCurrentClass($class);
        $systemSettings = $this->systemSettingsRepository->findOneBy([]);
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $data->setSchool($school);
            }
        }

        $previousSchool = !isset($studentData['previousSchool']) ? null : $this->schoolRepo->find($studentData['previousSchool']);
        $previousYear = !isset($studentData['previousYear']) ? null : $this->yearRepo->find($studentData['previousYear']);
        $previousClass = !isset($studentData['previousClass']) ? null : $this->classRepo->find($studentData['previousClass']);

        $hasInformationsChanged = ($previousClass !== $class)  || ($previousSchool !== $school) || ($previousYear !== $year);

        $result = null;
        if ($hasInformationsChanged){
            $this->studRegistrationController->deleteStudCourseReg($data ->getId());

            $result = $this->processor->process($data, $operation, $uriVariables, $context);
            $user = $this->getUser();
            $institution = $user->getInstitution();

            $studRegistration = $data; // $this->studRegistrationRepo->findOneBy([], ['id' => 'DESC']);
            $classPrograms  = $this->classProgramRepo->findBy(['institution' => $institution, 'class' => $class,  'year' => $year, 'school' => $school,'isSubjectObligatory'=>true]);

            foreach ($classPrograms as $classProgram){
                $existingCourseRegistration = $this->studentCourseRegistrationRepository->findOneBy([
                    'class' => $class,
                    'classProgram' => $classProgram,
                    'StudRegistration' => $studRegistration,
                ]);

                if (!$existingCourseRegistration) {
                    $studCourseRegistration = new StudentCourseRegistration();
                    $studCourseRegistration->setInstitution($institution);
                    $studCourseRegistration->setYear($year);
                    $studCourseRegistration->setClass($class);
                    $studCourseRegistration->setClassProgram($classProgram);
                    $evaluationPeriod = $classProgram->getEvaluationPeriod();
                    $studCourseRegistration->setEvaluationPeriod($evaluationPeriod);
                    $module = $classProgram->getModule();
                    $studCourseRegistration->setModule($module);
                    $studCourseRegistration->setStudRegistration($studRegistration);
//                    $studCourseRegistration->setSchool($school);
                    $studCourseRegistration->setUser($user);
                    $systemSettings = $this->systemSettingsRepository->findOneBy([]);
                    if($systemSettings) {
                        if ($systemSettings->isIsBranches()) {
                            $data->setSchool($school);
                        }
                    }
                    $this->manager->persist($studCourseRegistration);
                }
            }
            $this->manager->flush();
        }
        else $result = $this->processor->process($data, $operation, $uriVariables, $context);

        if ($data instanceof StudentRegistration){
            $joinCustomer = $this->manager->getRepository(Customer::class)->findOneBy(['studentRegistration' => $data]);
            if ($joinCustomer){
                $joinCustomer->setName($data->getStudent()->getFirstName() . ' '. $data->getStudent()->getName());
                $joinCustomer->setInstitution($this->getUser()->getInstitution());
                $joinCustomer->setUser($this->getUser());
                $joinCustomer->setYear($year);
                $joinCustomer->setCode($data->getStudent()->getFirstName());
                $joinCustomer->setPhone($data->getStudent()->getStudentphone());
                $joinCustomer->setEmail($data->getStudent()->getStudentemail());
                $joinCustomer->setUpdatedAt(new \DateTimeImmutable());
                $this->manager->persist($joinCustomer);
                }
            $this->manager->flush();
            }

        return $result;

    }

    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
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

}
