<?php

namespace App\Entity\School\Schooling\Registration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\GenerateMultipleCredentialController;
use App\Controller\GenerateSingleCredentialController;
use App\Controller\School\Schooling\Student\AttachStudentPictureController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\Location\Country;
use App\Entity\Setting\Person\BloodGroup;
use App\Entity\Setting\Person\Civility;
use App\Entity\Setting\Person\Religion;
use App\Entity\Setting\Person\Rhesus;
use App\Entity\Setting\Person\Sex;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\State\Processor\School\Schooling\Registration\PutStudentProfileProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
#[ORM\Table(name: 'school_student')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/student',
            normalizationContext: [
                'groups' => ['get:Student:collection'],
            ],
        ),
        new post(
            uriTemplate: '/attach/picture/student/{id}',
            controller: AttachStudentPictureController::class,
            openapiContext: [
                "summary" => "Create custom student with that resources.",
                "requestBody" => [
                    "description" => "Customization of our endpoint.",
                    "required" => true,
                    "content"=>[
                        "multipart/form-data" => [
                            "schema" => [
                                "type" => "object",
                                "properties" => [
                                    "matricule" => [
                                        "description" => "The matricule of the superhero",
                                        "type" => "string",
                                        "example" => "IUCI389898000",
                                    ],
                                    "othermatricule" => [
                                        "description" => "The othermatricule of the superhero",
                                        "type" => "string",
                                        "example" => "IUCI389898000",
                                    ],
                                    "name" => [
                                        "description" => "The name of the superhero",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "pob" => [
                                        "description" => "The pob of the superhero",
                                        "type" => "integer",
                                        "example" => "superman",
                                    ],
                                    "region" => [
                                        "description" => "The region of the superhero",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "studentphone" => [
                                        "description" => "The studentphone of the superhero",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "file" => [
                                        "type" => "string",
                                        "format" => "binary",
                                        "description" => "Upload a cover image of the superhero",
                                    ],
                                ],
                            ],

                        ]
                    ],

                    "responses"=>[
                        "200" => ["description" => "MyEntity resources created successfully."],
                        "400" => ["description" => "Invalid request body."],
                        "404" => ["description" => "MyEntity resources not found."]
                    ]

                ]],
            deserialize:false
        ),
        new put(
            uriTemplate: '/edit/student/{id}',
            denormalizationContext: [
                'groups' => ['write:Student'],
            ],
            processor: PutStudentProfileProcessor::class
        ),

        new Delete(
            uriTemplate: '/generate/credential/student/{id}',
            requirements: ['id' => '\d+'],
            controller: GenerateSingleCredentialController::class
        ),
        new Delete(
            uriTemplate: '/generate/credential/selected/student',
            controller: GenerateMultipleCredentialController::class,
            openapiContext: [
                "summary" => "Assign multiple MyEntity resources.",
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

class Student
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Student:collection', 'get:StudentRegistration:collection','get:StudentAttendance:collection', 'get:Payment:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?Year $year = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Matricule may not be blank')]
    #[Groups(['get:Student:collection', 'get:StudentRegistration:collection', 'write:Student'])]
    private ?string $matricule = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $othermatricule = null;

    #[ORM\Column(length: 33,  nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $internalmatricule = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection','get:StudentAttendance:collection', 'get:Payment:collection'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection', 'get:StudentAttendance:collection', 'get:Payment:collection'])]
    private ?string $firstName = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?Sex $sex = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?Religion $religion = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?\DateTimeImmutable $dob = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?\DateTimeImmutable $bornAround = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $pob = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?Country $country = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $region = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection', 'get:Payment:collection'])]
    private ?string $studentphone = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection', 'get:Payment:collection'])]
    private ?string $studentemail = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $studentProfession = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $studentDistrict = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $studentAddress = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $studentTown = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?BloodGroup $bloodGroup = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?Rhesus $rhesus = null;

    #[ORM\Column]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?bool $stutterer = null;

    #[ORM\Column]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?bool $leftHanded = null;

    #[ORM\Column]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?bool $hearingProblem = null;

    #[ORM\Column]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?bool $eyeProblem = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $vaccine = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $vaccineProhibited = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $medicalHistory = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $fathername = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $fatherphone = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $fatheremail = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $fatherprofession = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $mothername = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $motherphone = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $motheremail = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $motherprofession = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $guardianname = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $guardianphone = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $guardianemail = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $guardianprofession = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $partnerName = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $partnerPhone = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $partnerEmail = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $partnerProfession = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?Civility $civility = null;

    #[ORM\Column]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?int $numberOfChildren = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $status = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student'])]
    private ?string $picture = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student'])]
    private ?string $imageName = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student'])]
    private ?string $imageType = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student'])]
    private ?int $imageSize = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $operator = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(string $matricule): self
    {
        $this->matricule = $matricule;

        return $this;
    }

    public function getOthermatricule(): ?string
    {
        return $this->othermatricule;
    }

    public function setOthermatricule(?string $othermatricule): self
    {
        $this->othermatricule = $othermatricule;

        return $this;
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

    public function getInternalmatricule(): ?string
    {
        return $this->internalmatricule;
    }

    public function setInternalmatricule(?string $internalmatricule): self
    {
        $this->internalmatricule = $internalmatricule;

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
   

}
