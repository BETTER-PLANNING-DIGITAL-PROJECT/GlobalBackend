<?php

namespace App\Entity\School\Study\Teacher;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\GenerateMultipleCredentialController;
use App\Controller\GenerateSingleCredentialController;
use App\Controller\School\Study\Teacher\ImportTeacherController;
use App\Entity\School\Study\Configuration\Subject;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\Hr\EmploymentStatus;
use App\Entity\Setting\Location\Country;
use App\Entity\Setting\Location\Region;
use App\Entity\Setting\Person\BloodGroup;
use App\Entity\Setting\Person\Civility;
use App\Entity\Setting\Person\IdentityType;
use App\Entity\Setting\Person\MaritalStatus;
use App\Entity\Setting\Person\Religion;
use App\Entity\Setting\Person\Rhesus;
use App\Entity\Setting\Person\Sex;
use App\Entity\Setting\School\Diploma;
use App\Repository\School\Study\Teacher\TeacherRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TeacherRepository::class)]
#[ORM\Table(name: 'school_teacher')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/teacher',
            normalizationContext: [
                'groups' => ['get:Teacher:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/import/teacher',
            controller: ImportTeacherController::class,
            openapiContext: [
                "summary" => "Add multiple - resources.",
            ],
            denormalizationContext: [
                'groups' => ['post:"summary" => "Add multiple - resources.",']
            ],
        ),
        new Post(
            uriTemplate: '/create/teacher',
            denormalizationContext: [
                'groups' => ['write:Teacher'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/teacher/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Teacher'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/teacher/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/assign/teacher/{id}',
            requirements: ['id' => '\d+'],
            controller: GenerateSingleCredentialController::class
        ),
        new Delete(
            uriTemplate: '/assign/teachers',
            controller: GenerateMultipleCredentialController::class,
            openapiContext: [
                "summary" => "Deletes multiple MyEntity resources.",
                "requestBody" => [
                    "description" => "Array of MyEntity IDs to delete.",
                    "required" => true,
                    "content"=>[
                        "application/json"=>[
                            "schema"=> [
                                "type"=>"array",
                                "items"=> ["type"=>"integer"]
                            ],
                        ],
                    ]
                ],

                "responses"=>[
                    "204" => ["description" => "MyEntity resources deleted successfully."],
                    "400" => ["description" => "Invalid request body."],
                    "404" => ["description" => "MyEntity resources not found."]
                ]

            ]
        ),
    ]
)]
#[UniqueEntity(
    fields: ['email'],
    message: 'This teacher already exist in this institution',
    errorPath: 'teacher',
)]
#[UniqueEntity(
    fields: ['phone'],
    message: 'This teacher already exist in this institution',
    errorPath: 'teacher',
)]
class Teacher
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([ 'get:Teacher:collection','get:ClassProgram:collection', 'get:TeacherYearlyQuota:collection','get:LeaveRequest:collection','get:StudentAttendance:collection','get:StudentAttendance:collection','get:TimeTableModelDayCell:collection', 'get:CoursePermutation:collection', 'get:LeaveRequest:collection', 'get:TeacherCourseRegistration:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?Year $year = null;

    #[ORM\Column(length: 50, nullable: false)]
    #[Groups(['get:Teacher:collection', 'write:Teacher', 'get:TeacherYearlyQuota:collection'])]
    private ?string $registrationNumber = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:Teacher:collection', 'write:Teacher', 'get:ClassProgram:collection', 'get:LeaveRequest:collection', 'get:TeacherYearlyQuota:collection', 'get:StudentAttendance:collection', 'get:StudentAttendance:collection', 'get:TimeTableModelDayCell:collection', 'get:CoursePermutation:collection', 'get:LeaveRequest:collection', 'get:TeacherCourseRegistration:collection'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:Teacher:collection', 'write:Teacher', 'get:ClassProgram:collection', 'get:LeaveRequest:collection', 'get:TeacherYearlyQuota:collection', 'get:StudentAttendance:collection', 'get:StudentAttendance:collection', 'get:TimeTableModelDayCell:collection', 'get:CoursePermutation:collection', 'get:LeaveRequest:collection', 'get:TeacherCourseRegistration:collection'])]
    private ?string $firstName = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?\DateTimeInterface $dob = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $bornTowards = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $pob = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?Sex $sex = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?Civility $civility = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?Country $country = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?Religion $religion = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?Region $regionOrigin = null;

    #[ORM\Column(length: 50)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $phone = null;

    #[ORM\Column(length: 50)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $occupation = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $quarter = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $city = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $address = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?BloodGroup $bloodGroup = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?Rhesus $rhesus = null;

    /*#[ORM\Column]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?bool $stutterer = null;

    #[ORM\Column]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?bool $leftHanded = null;

    #[ORM\Column]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?bool $hearingProblem = null;

    #[ORM\Column]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?bool $eyeProblem = null;*/

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $vaccines = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $prohibited = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $medicalHistory = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $fatherName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $fatherPhone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $fatherEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $fatherOccupation = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $fatherCityResidence = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $motherName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $motherPhone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $motherEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $motherOccupation = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $motherCityResidence = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $partnerName = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $partnerPhone = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $partnerEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $partnerOccupation = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $partnerCityResidence = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?MaritalStatus $maritalStatus = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?int $numberOfChildren = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?Diploma $diploma = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $speciality = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?EmploymentStatus $employmentStatus = null;

    #[ORM\ManyToMany(targetEntity: Subject::class)]
    #[ORM\JoinTable(name: 'school_teacher_subject')]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private Collection $subjects;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?float $baseSalary = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $taxCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $socialCode = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?IdentityType $identityType = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $idNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Teacher:collection', 'write:Teacher'])]
    private ?string $placeOfIssue = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $issueAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $expirationAt = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $operator = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
        $this->subjects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(string $registrationNumber): static
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->firstName. ' ' .$this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getSex(): ?Sex
    {
        return $this->sex;
    }

    public function setSex(?Sex $sex): self
    {
        $this->sex = $sex;

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

    public function getDob(): ?\DateTimeInterface
    {
        return $this->dob;
    }

    public function setDob(\DateTimeInterface $dob): self
    {
        $this->dob = $dob;

        return $this;
    }


    public function getBornTowards(): ?string
    {
        return $this->bornTowards;
    }

    public function setBornTowards(?string $bornTowards): static
    {
        $this->bornTowards = $bornTowards;

        return $this;
    }

    public function getPob(): ?string
    {
        return $this->pob;
    }

    public function setPob(string $pob): static
    {
        $this->pob = $pob;

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getOccupation(): ?string
    {
        return $this->occupation;
    }

    public function setOccupation(?string $occupation): static
    {
        $this->occupation = $occupation;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getQuarter(): ?string
    {
        return $this->quarter;
    }

    public function setQuarter(?string $quarter): static
    {
        $this->quarter = $quarter;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getMaritalStatus(): ?MaritalStatus
    {
        return $this->maritalStatus;
    }

    public function setMaritalStatus(?MaritalStatus $maritalStatus): self
    {
        $this->maritalStatus = $maritalStatus;

        return $this;
    }

    public function getPartnerName(): ?string
    {
        return $this->partnerName;
    }

    public function setPartnerName(?string $partnerName): static
    {
        $this->partnerName = $partnerName;

        return $this;
    }

    public function getPartnerPhone(): ?string
    {
        return $this->partnerPhone;
    }

    public function setPartnerPhone(?string $partnerPhone): static
    {
        $this->partnerPhone = $partnerPhone;

        return $this;
    }

    public function getPartnerEmail(): ?string
    {
        return $this->partnerEmail;
    }

    public function setPartnerEmail(?string $partnerEmail): static
    {
        $this->partnerEmail = $partnerEmail;

        return $this;
    }

    public function getNumberOfChildren(): ?int
    {
        return $this->numberOfChildren;
    }

    public function setNumberOfChildren(?int $numberOfChildren): static
    {
        $this->numberOfChildren = $numberOfChildren;

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

    public function getSpeciality(): ?string
    {
        return $this->speciality;
    }

    public function setSpeciality(?string $speciality): static
    {
        $this->speciality = $speciality;

        return $this;
    }

    public function getIdentityType(): ?IdentityType
    {
        return $this->identityType;
    }

    public function setIdentityType(?IdentityType $identityType): self
    {
        $this->identityType = $identityType;

        return $this;
    }

    public function getIdNumber(): ?string
    {
        return $this->idNumber;
    }

    public function setIdNumber(?string $idNumber): static
    {
        $this->idNumber = $idNumber;

        return $this;
    }

    public function getPlaceOfIssue(): ?string
    {
        return $this->placeOfIssue;
    }

    public function setPlaceOfIssue(?string $placeOfIssue): static
    {
        $this->placeOfIssue = $placeOfIssue;

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

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): self
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

    /*public function isStutterer(): ?bool
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
    }*/

    public function getVaccines(): ?string
    {
        return $this->vaccines;
    }

    public function setVaccines(?string $vaccines): self
    {
        $this->vaccines = $vaccines;

        return $this;
    }

    public function getProhibited(): ?string
    {
        return $this->prohibited;
    }

    public function setProhibited(?string $prohibited): self
    {
        $this->prohibited = $prohibited;

        return $this;
    }

    public function getMedicalHistory(): ?string
    {
        return $this->medicalHistory;
    }

    public function setMedicalHistory(?string $medicalHistory): self
    {
        $this->medicalHistory = $medicalHistory;

        return $this;
    }

    public function getFatherName(): ?string
    {
        return $this->fatherName;
    }

    public function setFatherName(?string $fatherName): self
    {
        $this->fatherName = $fatherName;

        return $this;
    }

    public function getFatherPhone(): ?string
    {
        return $this->fatherPhone;
    }

    public function setFatherPhone(?string $fatherPhone): self
    {
        $this->fatherPhone = $fatherPhone;

        return $this;
    }

    public function getFatherEmail(): ?string
    {
        return $this->fatherEmail;
    }

    public function setFatherEmail(?string $fatherEmail): self
    {
        $this->fatherEmail = $fatherEmail;

        return $this;
    }

    public function getFatherOccupation(): ?string
    {
        return $this->fatherOccupation;
    }

    public function setFatherOccupation(?string $fatherOccupation): self
    {
        $this->fatherOccupation = $fatherOccupation;

        return $this;
    }

    public function getFatherCityResidence(): ?string
    {
        return $this->fatherCityResidence;
    }

    public function setFatherCityResidence(?string $fatherCityResidence): self
    {
        $this->fatherCityResidence = $fatherCityResidence;

        return $this;
    }

    public function getMotherName(): ?string
    {
        return $this->motherName;
    }

    public function setMotherName(?string $motherName): self
    {
        $this->motherName = $motherName;

        return $this;
    }

    public function getMotherPhone(): ?string
    {
        return $this->motherPhone;
    }

    public function setMotherPhone(?string $motherPhone): self
    {
        $this->motherPhone = $motherPhone;

        return $this;
    }

    public function getMotherEmail(): ?string
    {
        return $this->motherEmail;
    }

    public function setMotherEmail(?string $motherEmail): self
    {
        $this->motherEmail = $motherEmail;

        return $this;
    }

    public function getMotherOccupation(): ?string
    {
        return $this->motherOccupation;
    }

    public function setMotherOccupation(?string $motherOccupation): self
    {
        $this->motherOccupation = $motherOccupation;

        return $this;
    }

    public function getMotherCityResidence(): ?string
    {
        return $this->motherCityResidence;
    }

    public function setMotherCityResidence(?string $motherCityResidence): self
    {
        $this->motherCityResidence = $motherCityResidence;

        return $this;
    }

    public function getPartnerOccupation(): ?string
    {
        return $this->partnerOccupation;
    }

    public function setPartnerOccupation(?string $partnerOccupation): self
    {
        $this->partnerOccupation = $partnerOccupation;

        return $this;
    }

    public function getPartnerCityResidence(): ?string
    {
        return $this->partnerCityResidence;
    }

    public function setPartnerCityResidence(?string $partnerCityResidence): self
    {
        $this->partnerCityResidence = $partnerCityResidence;

        return $this;
    }

    public function getEmploymentStatus(): ?EmploymentStatus
    {
        return $this->employmentStatus;
    }

    public function setEmploymentStatus(?EmploymentStatus $employmentStatus): self
    {
        $this->employmentStatus = $employmentStatus;

        return $this;
    }

    public function getBaseSalary(): ?float
    {
        return $this->baseSalary;
    }

    public function setBaseSalary(?float $baseSalary): self
    {
        $this->baseSalary = $baseSalary;

        return $this;
    }

    public function getTaxCode(): ?string
    {
        return $this->taxCode;
    }

    public function setTaxCode(?string $taxCode): self
    {
        $this->taxCode = $taxCode;

        return $this;
    }

    public function getSocialCode(): ?string
    {
        return $this->socialCode;
    }

    public function setSocialCode(?string $socialCode): self
    {
        $this->socialCode = $socialCode;

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

    public function getRegionOrigin(): ?Region
    {
        return $this->regionOrigin;
    }

    public function setRegionOrigin(?Region $regionOrigin): self
    {
        $this->regionOrigin = $regionOrigin;

        return $this;
    }

    /**
     * @return Collection<int, Subject>
     */
    public function getSubjects(): Collection
    {
        return $this->subjects;
    }

    public function addSubject(Subject $subject): self
    {
        if (!$this->subjects->contains($subject)) {
            $this->subjects->add($subject);
        }

        return $this;
    }

    public function removeSubject(Subject $subject): self
    {
        $this->subjects->removeElement($subject);

        return $this;
    }

    public function getOperator(): ?User
    {
        return $this->operator;
    }

    public function setOperator(?User $user): static
    {
        $this->operator = $user;

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

    public function getIssueAt(): ?\DateTimeInterface
    {
        return $this->issueAt;
    }

    public function setIssueAt(?\DateTimeInterface $issueAt): self
    {
        $this->issueAt = $issueAt;

        return $this;
    }

    public function getExpirationAt(): ?\DateTimeInterface
    {
        return $this->expirationAt;
    }

    public function setExpirationAt(?\DateTimeInterface $expirationAt): self
    {
        $this->expirationAt = $expirationAt;

        return $this;
    }
}
