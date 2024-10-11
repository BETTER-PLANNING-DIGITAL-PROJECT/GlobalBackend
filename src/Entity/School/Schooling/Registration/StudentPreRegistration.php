<?php

namespace App\Entity\School\Schooling\Registration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\School\Schooling\Registration\ValidateStudentOnlineController;
use App\Entity\School\Schooling\Configuration\Cycle;
use App\Entity\School\Schooling\Configuration\Level;
use App\Entity\School\Schooling\Configuration\Option;
use App\Entity\School\Schooling\Configuration\Regime;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Configuration\Speciality;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\Location\Country;
use App\Entity\Setting\Person\BloodGroup;
use App\Entity\Setting\Person\Civility;
use App\Entity\Setting\Person\Religion;
use App\Entity\Setting\Person\Rhesus;
use App\Entity\Setting\Person\Sex;
use App\Entity\Setting\School\Diploma;
use App\Entity\Setting\School\Repeating;
use App\Repository\School\Schooling\Registration\StudentPreRegistrationRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StudentPreRegistrationRepository::class)]
#[ORM\Table(name: 'schooling_student_pre_registration')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/student-pre-registration',
            normalizationContext: [
                'groups' => ['get:StudentPreRegistration:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/student-pre-registration',
            denormalizationContext: [
                'groups' => ['write:StudentPreRegistration'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/student-pre-registration/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:StudentPreRegistration'],
            ],
        ),

        new Delete(
            uriTemplate: '/register/student/{id}',
            requirements: ['id' => '\d+'],
            controller: ValidateStudentOnlineController::class
        ),

        new Delete(
            uriTemplate: '/delete/student-pre-registration/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
class StudentPreRegistration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?Year $year = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Name cannot be smaller than {{ limit }} characters',
        maxMessage: 'Name cannot be longer than {{ limit }} characters',
    )]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $firstName = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?Sex $sex = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?Religion $religion = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?\DateTimeImmutable $dob = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?\DateTimeImmutable $bornAround = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $pob = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?Country $country = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $region = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $studentphone = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $studentemail = null;

    #[ORM\Column(length: 33)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $studentProfession = null;

    #[ORM\Column(length: 33)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $studentDistrict = null;

    #[ORM\Column(length: 33)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $studentAddress = null;

    #[ORM\Column(length: 33)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $studentTown = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?BloodGroup $bloodGroup = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?Rhesus $rhesus = null;

    #[ORM\Column]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?bool $stutterer = null;

    #[ORM\Column]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?bool $leftHanded = null;

    #[ORM\Column]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?bool $hearingProblem = null;

    #[ORM\Column]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?bool $eyeProblem = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $vaccine = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $vaccineProhibited = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $medicalHistory = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $fathername = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $fatherphone = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $fatheremail = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $fatherprofession = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $mothername = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $motherphone = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $motheremail = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $motherprofession = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $guardianname = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $guardianphone = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $guardianemail = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $guardianprofession = null;

    #[ORM\Column(length: 33)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $partnerName = null;

    #[ORM\Column(length: 33)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $partnerPhone = null;

    #[ORM\Column(length: 33)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $partnerEmail = null;

    #[ORM\Column(length: 33)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $partnerProfession = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?Civility $civility = null;

    #[ORM\Column]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?int $numberOfChildren = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $picture = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $imageName = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $imageType = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?int $imageSize = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $center = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $pvdiplome = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $pvselection = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?Repeating $repeating = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?float $average = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?School $school = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?Option $options = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?Diploma $diploma = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?Regime $regime = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?SchoolClass $classe = null;

    #[ORM\Column(length: 33,  nullable: true)]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?string $ranks = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?Cycle $cycle = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?Level $level = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentPreRegistration:collection', 'write:StudentPreRegistration'])]
    private ?Speciality $speciality = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDob(): ?\DateTimeImmutable
    {
        return $this->dob;
    }

    public function setDob(\DateTimeImmutable $dob): self
    {
        $this->dob = $dob;

        return $this;
    }

    public function getPob(): ?string
    {
        return $this->pob;
    }

    public function setPob(string $pob): self
    {
        $this->pob = $pob;

        return $this;
    }

    public function getSex(): ?Sex
    {
        return $this->sex;
    }

    public function setSex(?Sex $sex): self
    {
        $this->sex = $sex;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getReligion(): ?Religion
    {
        return $this->religion;
    }

    public function setReligion(?Religion $religion): self
    {
        $this->religion = $religion;

        return $this;
    }

    public function getStudentphone(): ?string
    {
        return $this->studentphone;
    }

    public function setStudentphone(string $studentphone): self
    {
        $this->studentphone = $studentphone;

        return $this;
    }

    public function getStudentemail(): ?string
    {
        return $this->studentemail;
    }

    public function setStudentemail(?string $studentemail): self
    {
        $this->studentemail = $studentemail;

        return $this;
    }

    public function getFathername(): ?string
    {
        return $this->fathername;
    }

    public function setFathername(string $fathername): self
    {
        $this->fathername = $fathername;

        return $this;
    }

    public function getFatherphone(): ?string
    {
        return $this->fatherphone;
    }

    public function setFatherphone(string $fatherphone): self
    {
        $this->fatherphone = $fatherphone;

        return $this;
    }

    public function getFatheremail(): ?string
    {
        return $this->fatheremail;
    }

    public function setFatheremail(string $fatheremail): self
    {
        $this->fatheremail = $fatheremail;

        return $this;
    }

    public function getFatherprofession(): ?string
    {
        return $this->fatherprofession;
    }

    public function setFatherprofession(string $fatherprofession): self
    {
        $this->fatherprofession = $fatherprofession;

        return $this;
    }

    public function getMothername(): ?string
    {
        return $this->mothername;
    }

    public function setMothername(string $mothername): self
    {
        $this->mothername = $mothername;

        return $this;
    }

    public function getMotherphone(): ?string
    {
        return $this->motherphone;
    }

    public function setMotherphone(string $motherphone): self
    {
        $this->motherphone = $motherphone;

        return $this;
    }

    public function getMotheremail(): ?string
    {
        return $this->motheremail;
    }

    public function setMotheremail(string $motheremail): self
    {
        $this->motheremail = $motheremail;

        return $this;
    }

    public function getMotherprofession(): ?string
    {
        return $this->motherprofession;
    }

    public function setMotherprofession(string $motherprofession): self
    {
        $this->motherprofession = $motherprofession;

        return $this;
    }

    public function getGuardianname(): ?string
    {
        return $this->guardianname;
    }

    public function setGuardianname(string $guardianname): self
    {
        $this->guardianname = $guardianname;

        return $this;
    }

    public function getGuardianphone(): ?string
    {
        return $this->guardianphone;
    }

    public function setGuardianphone(string $guardianphone): self
    {
        $this->guardianphone = $guardianphone;

        return $this;
    }

    public function getGuardianemail(): ?string
    {
        return $this->guardianemail;
    }

    public function setGuardianemail(string $guardianemail): self
    {
        $this->guardianemail = $guardianemail;

        return $this;
    }

    public function getGuardianprofession(): ?string
    {
        return $this->guardianprofession;
    }

    public function setGuardianprofession(string $guardianprofession): self
    {
        $this->guardianprofession = $guardianprofession;

        return $this;
    }

    public function isIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(bool $is_enable): self
    {
        $this->is_enable = $is_enable;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getImageType(): ?string
    {
        return $this->imageType;
    }

    public function setImageType(?string $imageType): self
    {
        $this->imageType = $imageType;

        return $this;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function setImageSize(?int $imageSize): self
    {
        $this->imageSize = $imageSize;

        return $this;
    }


    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getBornAround(): ?\DateTimeImmutable
    {
        return $this->bornAround;
    }

    public function setBornAround(\DateTimeImmutable $bornAround): self
    {
        $this->bornAround = $bornAround;

        return $this;
    }

    public function getStudentProfession(): ?string
    {
        return $this->studentProfession;
    }

    public function setStudentProfession(string $studentProfession): self
    {
        $this->studentProfession = $studentProfession;

        return $this;
    }

    public function getStudentDistrict(): ?string
    {
        return $this->studentDistrict;
    }

    public function setStudentDistrict(string $studentDistrict): self
    {
        $this->studentDistrict = $studentDistrict;

        return $this;
    }

    public function getStudentAddress(): ?string
    {
        return $this->studentAddress;
    }

    public function setStudentAddress(string $studentAddress): self
    {
        $this->studentAddress = $studentAddress;

        return $this;
    }

    public function getStudentTown(): ?string
    {
        return $this->studentTown;
    }

    public function setStudentTown(string $studentTown): self
    {
        $this->studentTown = $studentTown;

        return $this;
    }

    public function getBloodGroup(): ?BloodGroup
    {
        return $this->bloodGroup;
    }

    public function setBloodGroup(?BloodGroup $bloodGroup): self
    {
        $this->bloodGroup = $bloodGroup;

        return $this;
    }

    public function getRhesus(): ?Rhesus
    {
        return $this->rhesus;
    }

    public function setRhesus(?Rhesus $rhesus): self
    {
        $this->rhesus = $rhesus;

        return $this;
    }

    public function isStutterer(): ?bool
    {
        return $this->stutterer;
    }

    public function setStutterer(bool $stutterer): self
    {
        $this->stutterer = $stutterer;

        return $this;
    }

    public function isLeftHanded(): ?bool
    {
        return $this->leftHanded;
    }

    public function setLeftHanded(bool $leftHanded): self
    {
        $this->leftHanded = $leftHanded;

        return $this;
    }

    public function isHearingProblem(): ?bool
    {
        return $this->hearingProblem;
    }

    public function setHearingProblem(bool $hearingProblem): self
    {
        $this->hearingProblem = $hearingProblem;

        return $this;
    }

    public function isEyeProblem(): ?bool
    {
        return $this->eyeProblem;
    }

    public function setEyeProblem(bool $eyeProblem): self
    {
        $this->eyeProblem = $eyeProblem;

        return $this;
    }

    public function getVaccine(): ?string
    {
        return $this->vaccine;
    }

    public function setVaccine(string $vaccine): self
    {
        $this->vaccine = $vaccine;

        return $this;
    }

    public function getVaccineProhibited(): ?string
    {
        return $this->vaccineProhibited;
    }

    public function setVaccineProhibited(string $vaccineProhibited): self
    {
        $this->vaccineProhibited = $vaccineProhibited;

        return $this;
    }

    public function getMedicalHistory(): ?string
    {
        return $this->medicalHistory;
    }

    public function setMedicalHistory(string $medicalHistory): self
    {
        $this->medicalHistory = $medicalHistory;

        return $this;
    }

    public function getPartnerName(): ?string
    {
        return $this->partnerName;
    }

    public function setPartnerName(string $partnerName): self
    {
        $this->partnerName = $partnerName;

        return $this;
    }

    public function getPartnerPhone(): ?string
    {
        return $this->partnerPhone;
    }

    public function setPartnerPhone(string $partnerPhone): self
    {
        $this->partnerPhone = $partnerPhone;

        return $this;
    }

    public function getPartnerEmail(): ?string
    {
        return $this->partnerEmail;
    }

    public function setPartnerEmail(string $partnerEmail): self
    {
        $this->partnerEmail = $partnerEmail;

        return $this;
    }

    public function getPartnerProfession(): ?string
    {
        return $this->partnerProfession;
    }

    public function setPartnerProfession(string $partnerProfession): self
    {
        $this->partnerProfession = $partnerProfession;

        return $this;
    }

    public function getNumberOfChildren(): ?int
    {
        return $this->numberOfChildren;
    }

    public function setNumberOfChildren(int $numberOfChildren): self
    {
        $this->numberOfChildren = $numberOfChildren;

        return $this;
    }

    public function getCivility(): ?Civility
    {
        return $this->civility;
    }

    public function setCivility(?Civility $civility): self
    {
        $this->civility = $civility;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): static
    {
        $this->institution = $institution;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getCenter(): ?string
    {
        return $this->center;
    }

    public function setCenter(string $center): self
    {
        $this->center = $center;

        return $this;
    }

    public function getPvdiplome(): ?string
    {
        return $this->pvdiplome;
    }

    public function setPvdiplome(string $pvdiplome): self
    {
        $this->pvdiplome = $pvdiplome;

        return $this;
    }

    public function getPvselection(): ?string
    {
        return $this->pvselection;
    }

    public function setPvselection(string $pvselection): self
    {
        $this->pvselection = $pvselection;

        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): self
    {
        $this->school = $school;

        return $this;
    }


    public function getRepeating(): ?Repeating
    {
        return $this->repeating;
    }

    public function setRepeating(?Repeating $repeating): self
    {
        $this->repeating = $repeating;

        return $this;
    }


    public function getOptions(): ?Option
    {
        return $this->options;
    }

    public function setOptions(?Option $options): self
    {
        $this->options = $options;

        return $this;
    }


    public function getDiploma(): ?Diploma
    {
        return $this->diploma;
    }

    public function setDiploma(?Diploma $diploma): self
    {
        $this->diploma = $diploma;

        return $this;
    }

    public function getRegime(): ?Regime
    {
        return $this->regime;
    }

    public function setRegime(?Regime $regime): self
    {
        $this->regime = $regime;

        return $this;
    }

    public function getClasse(): ?SchoolClass
    {
        return $this->classe;
    }

    public function setClasse(?SchoolClass $classe): self
    {
        $this->classe = $classe;

        return $this;
    }

    public function getRanks(): ?string
    {
        return $this->ranks;
    }

    public function setRanks(string $ranks): self
    {
        $this->ranks = $ranks;

        return $this;
    }

    public function getAverage(): ?float
    {
        return $this->average;
    }

    public function setAverage(float $average): self
    {
        $this->average = $average;

        return $this;
    }

    public function getCycle(): ?Cycle
    {
        return $this->cycle;
    }

    public function setCycle(?Cycle $cycle): self
    {
        $this->cycle = $cycle;

        return $this;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getSpeciality(): ?Speciality
    {
        return $this->speciality;
    }

    public function setSpeciality(?Speciality $speciality): self
    {
        $this->speciality = $speciality;

        return $this;
    }
}
