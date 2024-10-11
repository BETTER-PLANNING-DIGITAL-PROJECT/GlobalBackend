<?php

namespace App\Entity\Billing\School;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Security\CurrentYearController;
use App\Entity\Billing\InvoiceArea;
use App\Entity\Billing\InvoicePeriod;
use App\Entity\Billing\InvoiceSection;
use App\Entity\Billing\InvoiceStatus;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Configuration\Speciality;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Billing\School\SpecificInvoiceRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SpecificInvoiceRepository::class)]
#[ORM\Table(name: 'billing_school_specific_invoice')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/specific-invoice',
            normalizationContext: [
                'groups' => ['get:SpecificInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/current-year',
            controller: CurrentYearController::class,
            normalizationContext: [
                'groups' => ['get:SpecificInvoice:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/specific-invoice',
            denormalizationContext: [
                'groups' => ['write:SpecificInvoice'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/specific-invoice/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:SpecificInvoice'],
            ],
        ),
        /*new Delete(
            uriTemplate: '/edit/specific-invoice-validate/{id}',
            requirements: ['id' => '\d+'],
            controller: InvoiceValidateController::class,
        ),
        new Delete(
            uriTemplate: '/edit/specific-invoice-un-validate/{id}',
            requirements: ['id' => '\d+'],
            controller: InvoiceUnValidateController::class,

        ),*/
        new Delete(
            uriTemplate: '/delete/specific-invoice/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
class SpecificInvoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:SpecificInvoice:collection','get:Collection:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice'])]
    private ?Year $year = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice'])]
    private ?School $school = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Collection:collection', 'write:Collection'])]
    private ?SchoolClass $class = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice'])]
    private ?Speciality $speciality = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice'])]
    private ?StudentRegistration $student = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice'])]
    private ?InvoiceSection $invoiceSection = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice'])]
    private ?InvoicePeriod $invoicePeriod = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice'])]
    private ?InvoiceStatus $invoiceStatus = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice'])]
    private ?InvoiceArea $invoiceArea = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice','get:Collection:collection'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice'])]
    private ?float $amount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice'])]
    private ?int $dueDate = null;
    #[ORM\Column]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice'])]
    private ?bool $isLimitedToRegistrants = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice'])]
    private ?bool $isNewStudentStatus = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice'])]
    private ?bool $isNonRepeater = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice'])]
    private ?bool $isRepeater = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice'])]
    private ?bool $isCompleteRegistration = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice'])]
    private ?bool $isSubjectRegistration = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice'])]
    private ?bool $isIntermediateClass = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice'])]
    private ?bool $isOptionalReview = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice'])]
    private ?bool $isCompulsoryExam = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecificInvoice:collection', 'write:SpecificInvoice'])]
    private ?bool $isValidated = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;


    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
        $this->isValidated = false;
        $this->isNotValidated = false;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): self
    {
        $this->school = $school;

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

    public function getStudent(): ?StudentRegistration
    {
        return $this->student;
    }

    public function setStudent(?StudentRegistration $student): self
    {
        $this->student = $student;

        return $this;
    }

    public function isIsLimitedToRegistrants(): ?bool
    {
        return $this->isLimitedToRegistrants;
    }

    public function setIsLimitedToRegistrants(bool $isLimitedToRegistrants): self
    {
        $this->isLimitedToRegistrants = $isLimitedToRegistrants;

        return $this;
    }

    public function isIsNewStudentStatus(): ?bool
    {
        return $this->isNewStudentStatus;
    }

    public function setIsNewStudentStatus(?bool $isNewStudentStatus): self
    {
        $this->isNewStudentStatus = $isNewStudentStatus;

        return $this;
    }

    public function isIsNonRepeater(): ?bool
    {
        return $this->isNonRepeater;
    }

    public function setIsNonRepeater(?bool $isNonRepeater): self
    {
        $this->isNonRepeater = $isNonRepeater;

        return $this;
    }

    public function isIsRepeater(): ?bool
    {
        return $this->isRepeater;
    }

    public function setIsRepeater(?bool $isRepeater): self
    {
        $this->isRepeater = $isRepeater;

        return $this;
    }

    public function isIsCompleteRegistration(): ?bool
    {
        return $this->isCompleteRegistration;
    }

    public function setIsCompleteRegistration(?bool $isCompleteRegistration): self
    {
        $this->isCompleteRegistration = $isCompleteRegistration;

        return $this;
    }

    public function isIsSubjectRegistration(): ?bool
    {
        return $this->isSubjectRegistration;
    }

    public function setIsSubjectRegistration(?bool $isSubjectRegistration): self
    {
        $this->isSubjectRegistration = $isSubjectRegistration;

        return $this;
    }

    public function isIsIntermediateClass(): ?bool
    {
        return $this->isIntermediateClass;
    }

    public function setIsIntermediateClass(?bool $isIntermediateClass): self
    {
        $this->isIntermediateClass = $isIntermediateClass;

        return $this;
    }

    public function isIsOptionalReview(): ?bool
    {
        return $this->isOptionalReview;
    }

    public function setIsOptionalReview(?bool $isOptionalReview): self
    {
        $this->isOptionalReview = $isOptionalReview;

        return $this;
    }

    public function isIsCompulsoryExam(): ?bool
    {
        return $this->isCompulsoryExam;
    }

    public function setIsCompulsoryExam(?bool $isCompulsoryExam): self
    {
        $this->isCompulsoryExam = $isCompulsoryExam;

        return $this;
    }

    public function isIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(bool $is_enable): static
    {
        $this->is_enable = $is_enable;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

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

    public function getInvoiceSection(): ?InvoiceSection
    {
        return $this->invoiceSection;
    }
    public function getClass(): ?SchoolClass
    {
        return $this->class;
    }

    public function setClass(?SchoolClass $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function setInvoiceSection(?InvoiceSection $invoiceSection): self
    {
        $this->invoiceSection = $invoiceSection;

        return $this;
    }


    public function getInvoiceArea(): ?InvoiceArea
    {
        return $this->invoiceArea;
    }

    public function setInvoiceArea(?InvoiceArea $invoiceArea): self
    {
        $this->invoiceArea = $invoiceArea;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDueDate(): ?int
    {
        return $this->dueDate;
    }

    public function setDueDate(?int $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getInvoiceStatus(): ?InvoiceStatus
    {
        return $this->invoiceStatus;
    }

    public function setInvoiceStatus(?InvoiceStatus $invoiceStatus): self
    {
        $this->invoiceStatus = $invoiceStatus;

        return $this;
    }

    public function getInvoicePeriod(): ?InvoicePeriod
    {
        return $this->invoicePeriod;
    }

    public function setInvoicePeriod(?InvoicePeriod $invoicePeriod): self
    {
        $this->invoicePeriod = $invoicePeriod;

        return $this;
    }

    public function isIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(?bool $isValidated): self
    {
        $this->isValidated = $isValidated;

        return $this;
    }

}
