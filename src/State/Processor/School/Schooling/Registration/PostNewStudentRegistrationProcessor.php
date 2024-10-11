<?php
namespace App\State\Processor\School\Schooling\Registration;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Partner\Customer;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\Institution\InstitutionRepository;
use App\Repository\Security\Session\YearRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\School\Schooling\Registration\Student;
use App\Repository\Setting\Location\CountryRepository;
use App\Repository\Setting\Person\SexRepository;
use App\Repository\Setting\Person\ReligionRepository;
use App\Repository\Setting\Person\BloodGroupRepository;
use App\Repository\Setting\Person\RhesusRepository;
use App\Repository\Setting\Person\CivilityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PostNewStudentRegistrationProcessor implements ProcessorInterface
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
    private  BranchRepository $branchRepository;
   private SystemSettingsRepository $systemSettingsRepository;

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                Request $request, RhesusRepository $rhesusRepo,
                                EntityManagerInterface $manager,
                                CountryRepository $countryRepo,
                                BloodGroupRepository $bloodGroupRepo,
                                StudentRepository $studentRepository,
                                BranchRepository $branchRepository,
                                SystemSettingsRepository $systemSettingsRepository,
                                SexRepository $sexRepo,
                                YearRepository $yearRepo,
                                ReligionRepository $religionRepo,
                                InstitutionRepository $institutionRepo,
                                CivilityRepository $civilityRepo,
                                SchoolClassRepository $classRepo,
                                SchoolRepository $schoolRepo,
                                ClassProgramRepository $classProgramRepo,
                                StudentRegistrationRepository $studentRegistrationRepo) {
        $this->req = $request;
        $this->manager = $manager;
        $this->yearRepo = $yearRepo;
        $this->countryRepo = $countryRepo;
        $this->bloodGroupRepo = $bloodGroupRepo;
        $this->studentRepository = $studentRepository;
        $this->rhesusRepo = $rhesusRepo;
        $this->civilityRepo = $civilityRepo;
        $this->sexRepo = $sexRepo;
        $this->religionRepo = $religionRepo;
        $this->institutionRepo = $institutionRepo;
        $this->classRepo = $classRepo;
        $this->schoolRepo = $schoolRepo;
        $this->classProgramRepo = $classProgramRepo;
        $this->studentRegistrationRepo = $studentRegistrationRepo;
        $this->branchRepository = $branchRepository;
        $this->systemSettingsRepository = $systemSettingsRepository;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $institution = $this->getUser()->getInstitution();
        $studentData = json_decode($this->req->getContent(), true);

        $matricule = $studentData['matricule'];

        // Check if student matricule already exist
        $student = $this->studentRepository->findOneBy(['institution' => $institution, 'matricule' => $matricule]);
        if($student)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'this matricule already exist.'], 400);
        }

        // Check if student other matricule already exist
        $otherMatricule = $studentData['othermatricule'];
        if ($otherMatricule) {
            $student = $this->studentRepository->findOneBy(['institution' => $institution, 'othermatricule' => $otherMatricule]);
            if ($student) {
                // Warning
                return new JsonResponse(['hydra:description' => 'this ministry matricule already exist.'], 400);
            }
        }

        // Check if student internal matricule already exist
        $internalMatricule = $studentData['internalmatricule'];
        if ($internalMatricule) {
            $student = $this->studentRepository->findOneBy(['institution' => $institution, 'internalmatricule' => $internalMatricule]);
            if ($student) {
                // Warning
                return new JsonResponse(['hydra:description' => 'this internal matricule already exist.'], 400);
            }
        }

        // Set Student

        $student = new Student();

        $year = !isset($studentData['year']) ? null : $this->yearRepo->find($this->getIdFromApiResourceId($studentData['year']));

        $student->setYear($year);
        $student->setCountry(!isset($studentData['country']) ? null : $this->countryRepo->find($this->getIdFromApiResourceId($studentData['country'])));
        $student->setMatricule($studentData['matricule']);
        $student->setOtherMatricule($studentData['othermatricule']);
        $student->setInternalmatricule($studentData['internalmatricule']);
        $student->setName($studentData['name']);
        $student->setFirstName($studentData['firstName']);
        $new = new \DateTimeImmutable($studentData['dob']);
        $student->setDob($new);
        // $new = new \DateTimeImmutable($studentData['bornAround']);
        // $student->setBornAround($new);
        $student->setPob($studentData['pob']);
        $student->setSex(!isset($studentData['sex']) ? null : $this->sexRepo->find($this->getIdFromApiResourceId($studentData['sex'])));
        $student->setRegion(!isset($studentData['region']) ? "" : $studentData['region']);
        $student->setReligion(!isset($studentData['religion']) ? null : $this->religionRepo->find($this->getIdFromApiResourceId($studentData['religion'])));
        $student->setBloodGroup(!isset($studentData['bloodGroup']) ? null : $this->bloodGroupRepo->find($this->getIdFromApiResourceId($studentData['bloodGroup'])));
        $student->setRhesus(!isset($studentData['rhesus']) ? null : $this->rhesusRepo->find($this->getIdFromApiResourceId($studentData['rhesus'])));

        $student->setStutterer($studentData['stutterer']);
        $student->setLeftHanded($studentData['leftHanded']);
        $student->setHearingProblem($studentData['hearingProblem']);
        $student->setEyeProblem($studentData['eyeProblem']);
        // $student->setVaccine($studentData['vaccine']);
        // $student->setVaccineProhibited($studentData['vaccineProhibited']);
        $student->setMedicalHistory($studentData['medicalHistory']);

        $student->setStudentphone($studentData['studentphone']);
        $student->setStudentemail($studentData['studentemail']);
        $student->setStudentProfession($studentData['studentProfession']);
        $student->setStudentDistrict($studentData['studentDistrict']);
        $student->setStudentAddress($studentData['studentAddress']);
        $student->setStudentTown($studentData['studentTown']);
        $student->setFathername($studentData['fathername']);
        $student->setFatherphone($studentData['fatherphone']);
        $student->setFatheremail($studentData['fatheremail']);
        $student->setFatherprofession($studentData['fatherprofession']);
        $student->setMothername($studentData['mothername']);
        $student->setMotherphone($studentData['motherphone']);
        $student->setMotheremail($studentData['motheremail']);
        $student->setMotherprofession($studentData['motherprofession']);
        $student->setGuardianname($studentData['guardianname']);
        $student->setGuardianphone($studentData['guardianphone']);
        $student->setGuardianemail($studentData['guardianemail']);
        $student->setGuardianprofession($studentData['guardianprofession']);
        
        $student->setPartnerName($studentData['partnerName']);
        $student->setPartnerPhone($studentData['partnerPhone']);
        $student->setPartnerEmail($studentData['partnerEmail']);
        $student->setPartnerProfession($studentData['partnerProfession']);
        // $student->setCivility(!isset($studentData['civility']) ? null : $this->civilityRepo->find($this->getIdFromApiResourceId($studentData['civility'])));
        $numberOfChildren = intval($studentData['numberOfChildren']);
        $student->setNumberOfChildren($numberOfChildren);
        $student->setInstitution($institution);
        $student->setStatus('registered');

        $this->manager->persist($student);

        // Set Student End


        // Set Student Registration

        $data->setStudent($student);

        $school = !isset($studentData['school']) ? null : $this->schoolRepo->find($this->getIdFromApiResourceId($studentData['school']));
        $class = !isset($studentData['classe']) ? null : $this->classRepo->find($this->getIdFromApiResourceId($studentData['classe']));

        $data->setCurrentYear($year);
        $data->setCurrentClass($class);
        $systemSettings = $this->systemSettingsRepository->findOneBy([]);
        $schools = $this->schoolRepo->findOneBy(['branch' => $this->getUser()->getBranch()]);
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $data->setSchool($school);
            } else {
                $data->setSchool($schools);
            }
        }

        $data->setInstitution($this->getUser()->getInstitution());
        $data->setYear($year);
        $data->setUser($this->getUser());
        $data->setStatus('registered');

        $result = $this->processor->process($data, $operation, $uriVariables, $context);

        // Set Student Registration End



        // Student Course Registration

        // Get the last student registration which is the most recent
        $studentRegistration = $this->studentRegistrationRepo->findOneBy([], ['id' => 'DESC']);

        // Get all the courses or class program
        $classPrograms  = $this->classProgramRepo->findBy(['institution' => $institution, 'class' => $class,  'year' => $year, 'school' => $school, 'isSubjectObligatory'=>true]);

        if($classPrograms)
        {
            // Set Student Course Registration
            foreach ($classPrograms as $classProgram)
            {
                // Check duplicate here before set !

                $studentCourseRegistration = new StudentCourseRegistration();

                $studentCourseRegistration->setYear($year);
                $studentCourseRegistration->setClass($class);
//                $studentCourseRegistration->setSchool($school);
                $studentCourseRegistration->setClassProgram($classProgram);
                $studentCourseRegistration->setEvaluationPeriod($classProgram->getEvaluationPeriod());
                if($classProgram->getModule()){
                    $studentCourseRegistration->setModule($classProgram->getModule());
                }
                $systemSettings = $this->systemSettingsRepository->findOneBy([]);
                $schools = $this->schoolRepo->findOneBy(['branch' => $this->getUser()->getBranch()]);
                if($systemSettings) {
                    if ($systemSettings->isIsBranches()) {
                        $studentCourseRegistration->setSchool($school);
                    } else {
                        $studentCourseRegistration->setSchool($schools);
                    }
                }
                $studentCourseRegistration->setStudRegistration($studentRegistration);

                $studentCourseRegistration->setInstitution($this->getUser()->getInstitution());
                $studentCourseRegistration->setUser($this->getUser());

                $this->manager->persist($studentCourseRegistration);
            }
        }

        // Student Course Registration End


        // Student Registration = Customer

        // Check duplicate here before set !

        $customer = new Customer();

        $customer->setCode($matricule);
        $customer->setName($student->getFirstName() .' '. $student->getName());
        $customer->setPhone($student->getStudentphone());
        $customer->setEmail($student->getStudentemail());
//        $customer->setStudentRegistration($data);

        $customer->setIsEnable(true);
        $customer->setCreatedAt(new \DateTimeImmutable());
        $customer->setUpdatedAt(new \DateTimeImmutable());
        $customer->setInstitution($this->getUser()->getInstitution());
        $customer->setUser($this->getUser());
        $customer->setYear($year);

        $this->manager->persist($customer);

        $this->manager->flush();
        if (!$data->getId()) {
            return $result;
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