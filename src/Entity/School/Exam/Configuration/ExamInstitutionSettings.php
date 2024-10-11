<?php

namespace App\Entity\School\Exam\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Repository\School\Exam\Configuration\ExamInstitutionSettingsRepository;
use App\State\Current\InstitutionProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExamInstitutionSettingsRepository::class)]
#[ORM\Table(name: 'school_exam_institution_settings')]
#[ApiResource(
    operations:[
        /*new Get(
            uriTemplate: '/exam-institution-settings/{id}/get',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:ExamInstitutionSettings:item'],
            ],
        ),*/
        new GetCollection(
            uriTemplate: '/exam-institution-settings/get',
            normalizationContext: [
                'groups' => ['get:ExamInstitutionSettings:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/exam-institution-settings/create',
            denormalizationContext: [
                'groups' => ['write:ExamInstitutionSettings'],
            ],
            processor: InstitutionProcessor::class,

        ),
        new Put(
            uriTemplate: '/exam-institution-settings/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:ExamInstitutionSettings'],
            ],
        ),
        new Delete(
            uriTemplate: '/exam-institution-settings/delete/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
class ExamInstitutionSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'institution NotNull')]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'year NotNull')]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?Year $year = null;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $displaySequencesInBulletins = true;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $remedialClassesForAllStudents = true;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $dontClassifyTheExcluded = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $showExcludedBallots = true;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $assign0WhenTheMarkIsNotEntered = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $theValidationBaseIsObligatory = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $activateMarkDeliberation = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $activateEliminationMarks = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $optimizingBulletinsBuffering = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $weightsWhenEnteringMarks = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $calculateAveragesForUnclassified = true;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $printByOrderOfMerit = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $calculateSubjectsRanks = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $activateClassCouncilsByComposition = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $accessToMarksFromPreviousYears = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $enableAtomicMarkEntry = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $activateWeightingsPerAssignment = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $markEntryBaseIsCoeff = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $printDuplicateExamReceipts = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $displayNumberOfRows = true;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $displayNumberOfRowsOnCourses = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $displayTheClassSizeInRows = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $detailHomeworkMarks = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $detailMarkTypes = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $detailDisciplineElements = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $displayNonBonusSubjects = true;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $calculateMarksForDaughterSubjects = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $limitCalculationsToExistingMarks = true;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $detailSubjectStatisticsForRegisteredStudents = true;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $catchUpMarkReplacesNormalMarkIfHigher = true;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $groupingMarksByMarkType = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $displayMarksInDisciplineScreens = true;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $displayMarksNotEnteredInTheBulletin = true;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $makeItObligatoryToEnterTheProgressionOfTimeVolumes = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $makeMarkEntryPeriodsObligatory = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $automaticallyRepeatMarksForRepeaters = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $automateStudentAverages = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $hideNamesIfAnonymityAvailable = true;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $automateStudentRanks = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $multipleTeacherRatings = false;

    #[ORM\Column]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?bool $showPhotoOnSummary = true;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ExamInstitutionSettings:collection', 'write:ExamInstitutionSettings'])]
    private ?float $entryBase = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
    private ?bool $is_archive = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = false;
        $this->is_archive = false;
        
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): ExamInstitutionSettings
    {
        $this->institution = $institution;
        return $this;
    }

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): ExamInstitutionSettings
    {
        $this->year = $year;
        return $this;
    }

    public function getDisplaySequencesInBulletins(): ?bool
    {
        return $this->displaySequencesInBulletins;
    }

    public function setDisplaySequencesInBulletins(?bool $displaySequencesInBulletins): ExamInstitutionSettings
    {
        $this->displaySequencesInBulletins = $displaySequencesInBulletins;
        return $this;
    }

    public function getRemedialClassesForAllStudents(): ?bool
    {
        return $this->remedialClassesForAllStudents;
    }

    public function setRemedialClassesForAllStudents(?bool $remedialClassesForAllStudents): ExamInstitutionSettings
    {
        $this->remedialClassesForAllStudents = $remedialClassesForAllStudents;
        return $this;
    }

    public function getDontClassifyTheExcluded(): ?bool
    {
        return $this->dontClassifyTheExcluded;
    }

    public function setDontClassifyTheExcluded(?bool $dontClassifyTheExcluded): ExamInstitutionSettings
    {
        $this->dontClassifyTheExcluded = $dontClassifyTheExcluded;
        return $this;
    }

    public function getShowExcludedBallots(): ?bool
    {
        return $this->showExcludedBallots;
    }

    public function setShowExcludedBallots(?bool $showExcludedBallots): ExamInstitutionSettings
    {
        $this->showExcludedBallots = $showExcludedBallots;
        return $this;
    }

    public function getAssign0WhenTheMarkIsNotEntered(): ?bool
    {
        return $this->assign0WhenTheMarkIsNotEntered;
    }

    public function setAssign0WhenTheMarkIsNotEntered(?bool $assign0WhenTheMarkIsNotEntered): ExamInstitutionSettings
    {
        $this->assign0WhenTheMarkIsNotEntered = $assign0WhenTheMarkIsNotEntered;
        return $this;
    }

    public function getTheValidationBaseIsObligatory(): ?bool
    {
        return $this->theValidationBaseIsObligatory;
    }

    public function setTheValidationBaseIsObligatory(?bool $theValidationBaseIsObligatory): ExamInstitutionSettings
    {
        $this->theValidationBaseIsObligatory = $theValidationBaseIsObligatory;
        return $this;
    }

    public function getActivateMarkDeliberation(): ?bool
    {
        return $this->activateMarkDeliberation;
    }

    public function setActivateMarkDeliberation(?bool $activateMarkDeliberation): ExamInstitutionSettings
    {
        $this->activateMarkDeliberation = $activateMarkDeliberation;
        return $this;
    }

    public function getActivateEliminationMarks(): ?bool
    {
        return $this->activateEliminationMarks;
    }

    public function setActivateEliminationMarks(?bool $activateEliminationMarks): ExamInstitutionSettings
    {
        $this->activateEliminationMarks = $activateEliminationMarks;
        return $this;
    }

    public function getOptimizingBulletinsBuffering(): ?bool
    {
        return $this->optimizingBulletinsBuffering;
    }

    public function setOptimizingBulletinsBuffering(?bool $optimizingBulletinsBuffering): ExamInstitutionSettings
    {
        $this->optimizingBulletinsBuffering = $optimizingBulletinsBuffering;
        return $this;
    }

    public function getWeightsWhenEnteringMarks(): ?bool
    {
        return $this->weightsWhenEnteringMarks;
    }

    public function setWeightsWhenEnteringMarks(?bool $weightsWhenEnteringMarks): ExamInstitutionSettings
    {
        $this->weightsWhenEnteringMarks = $weightsWhenEnteringMarks;
        return $this;
    }

    public function getCalculateAveragesForUnclassified(): ?bool
    {
        return $this->calculateAveragesForUnclassified;
    }

    public function setCalculateAveragesForUnclassified(?bool $calculateAveragesForUnclassified): ExamInstitutionSettings
    {
        $this->calculateAveragesForUnclassified = $calculateAveragesForUnclassified;
        return $this;
    }

    public function getPrintByOrderOfMerit(): ?bool
    {
        return $this->printByOrderOfMerit;
    }

    public function setPrintByOrderOfMerit(?bool $printByOrderOfMerit): ExamInstitutionSettings
    {
        $this->printByOrderOfMerit = $printByOrderOfMerit;
        return $this;
    }

    public function getCalculateSubjectsRanks(): ?bool
    {
        return $this->calculateSubjectsRanks;
    }

    public function setCalculateSubjectsRanks(?bool $calculateSubjectsRanks): ExamInstitutionSettings
    {
        $this->calculateSubjectsRanks = $calculateSubjectsRanks;
        return $this;
    }

    public function getActivateClassCouncilsByComposition(): ?bool
    {
        return $this->activateClassCouncilsByComposition;
    }

    public function setActivateClassCouncilsByComposition(?bool $activateClassCouncilsByComposition): ExamInstitutionSettings
    {
        $this->activateClassCouncilsByComposition = $activateClassCouncilsByComposition;
        return $this;
    }

    public function getAccessToMarksFromPreviousYears(): ?bool
    {
        return $this->accessToMarksFromPreviousYears;
    }

    public function setAccessToMarksFromPreviousYears(?bool $accessToMarksFromPreviousYears): ExamInstitutionSettings
    {
        $this->accessToMarksFromPreviousYears = $accessToMarksFromPreviousYears;
        return $this;
    }

    public function getEnableAtomicMarkEntry(): ?bool
    {
        return $this->enableAtomicMarkEntry;
    }

    public function setEnableAtomicMarkEntry(?bool $enableAtomicMarkEntry): ExamInstitutionSettings
    {
        $this->enableAtomicMarkEntry = $enableAtomicMarkEntry;
        return $this;
    }

    public function getActivateWeightingsPerAssignment(): ?bool
    {
        return $this->activateWeightingsPerAssignment;
    }

    public function setActivateWeightingsPerAssignment(?bool $activateWeightingsPerAssignment): ExamInstitutionSettings
    {
        $this->activateWeightingsPerAssignment = $activateWeightingsPerAssignment;
        return $this;
    }

    public function getPrintDuplicateExamReceipts(): ?bool
    {
        return $this->printDuplicateExamReceipts;
    }

    public function setPrintDuplicateExamReceipts(?bool $printDuplicateExamReceipts): ExamInstitutionSettings
    {
        $this->printDuplicateExamReceipts = $printDuplicateExamReceipts;
        return $this;
    }

    public function getDisplayNumberOfRows(): ?bool
    {
        return $this->displayNumberOfRows;
    }

    public function setDisplayNumberOfRows(?bool $displayNumberOfRows): ExamInstitutionSettings
    {
        $this->displayNumberOfRows = $displayNumberOfRows;
        return $this;
    }

    public function getDetailHomeworkMarks(): ?bool
    {
        return $this->detailHomeworkMarks;
    }

    public function setDetailHomeworkMarks(?bool $detailHomeworkMarks): ExamInstitutionSettings
    {
        $this->detailHomeworkMarks = $detailHomeworkMarks;
        return $this;
    }

    public function getDetailMarkTypes(): ?bool
    {
        return $this->detailMarkTypes;
    }

    public function setDetailMarkTypes(?bool $detailMarkTypes): ExamInstitutionSettings
    {
        $this->detailMarkTypes = $detailMarkTypes;
        return $this;
    }

    public function getDetailDisciplineElements(): ?bool
    {
        return $this->detailDisciplineElements;
    }

    public function setDetailDisciplineElements(?bool $detailDisciplineElements): ExamInstitutionSettings
    {
        $this->detailDisciplineElements = $detailDisciplineElements;
        return $this;
    }

    public function getDisplayNonBonusSubjects(): ?bool
    {
        return $this->displayNonBonusSubjects;
    }

    public function setDisplayNonBonusSubjects(?bool $displayNonBonusSubjects): ExamInstitutionSettings
    {
        $this->displayNonBonusSubjects = $displayNonBonusSubjects;
        return $this;
    }

    public function getCalculateMarksForDaughterSubjects(): ?bool
    {
        return $this->calculateMarksForDaughterSubjects;
    }

    public function setCalculateMarksForDaughterSubjects(?bool $calculateMarksForDaughterSubjects): ExamInstitutionSettings
    {
        $this->calculateMarksForDaughterSubjects = $calculateMarksForDaughterSubjects;
        return $this;
    }

    public function getLimitCalculationsToExistingMarks(): ?bool
    {
        return $this->limitCalculationsToExistingMarks;
    }

    public function setLimitCalculationsToExistingMarks(?bool $limitCalculationsToExistingMarks): ExamInstitutionSettings
    {
        $this->limitCalculationsToExistingMarks = $limitCalculationsToExistingMarks;
        return $this;
    }

    public function getDetailSubjectStatisticsForRegisteredStudents(): ?bool
    {
        return $this->detailSubjectStatisticsForRegisteredStudents;
    }

    public function setDetailSubjectStatisticsForRegisteredStudents(?bool $detailSubjectStatisticsForRegisteredStudents): ExamInstitutionSettings
    {
        $this->detailSubjectStatisticsForRegisteredStudents = $detailSubjectStatisticsForRegisteredStudents;
        return $this;
    }

    public function getCatchUpMarkReplacesNormalMarkIfHigher(): ?bool
    {
        return $this->catchUpMarkReplacesNormalMarkIfHigher;
    }

    public function setCatchUpMarkReplacesNormalMarkIfHigher(?bool $catchUpMarkReplacesNormalMarkIfHigher): ExamInstitutionSettings
    {
        $this->catchUpMarkReplacesNormalMarkIfHigher = $catchUpMarkReplacesNormalMarkIfHigher;
        return $this;
    }

    public function getGroupingMarksByMarkType(): ?bool
    {
        return $this->groupingMarksByMarkType;
    }

    public function setGroupingMarksByMarkType(?bool $groupingMarksByMarkType): ExamInstitutionSettings
    {
        $this->groupingMarksByMarkType = $groupingMarksByMarkType;
        return $this;
    }

    public function getDisplayMarksInDisciplineScreens(): ?bool
    {
        return $this->displayMarksInDisciplineScreens;
    }

    public function setDisplayMarksInDisciplineScreens(?bool $displayMarksInDisciplineScreens): ExamInstitutionSettings
    {
        $this->displayMarksInDisciplineScreens = $displayMarksInDisciplineScreens;
        return $this;
    }

    public function getDisplayMarksNotEnteredInTheBulletin(): ?bool
    {
        return $this->displayMarksNotEnteredInTheBulletin;
    }

    public function setDisplayMarksNotEnteredInTheBulletin(?bool $displayMarksNotEnteredInTheBulletin): ExamInstitutionSettings
    {
        $this->displayMarksNotEnteredInTheBulletin = $displayMarksNotEnteredInTheBulletin;
        return $this;
    }

    public function getMakeItObligatoryToEnterTheProgressionOfTimeVolumes(): ?bool
    {
        return $this->makeItObligatoryToEnterTheProgressionOfTimeVolumes;
    }

    public function setMakeItObligatoryToEnterTheProgressionOfTimeVolumes(?bool $makeItObligatoryToEnterTheProgressionOfTimeVolumes): ExamInstitutionSettings
    {
        $this->makeItObligatoryToEnterTheProgressionOfTimeVolumes = $makeItObligatoryToEnterTheProgressionOfTimeVolumes;
        return $this;
    }

    public function getMakeMarkEntryPeriodsObligatory(): ?bool
    {
        return $this->makeMarkEntryPeriodsObligatory;
    }

    public function setMakeMarkEntryPeriodsObligatory(?bool $makeMarkEntryPeriodsObligatory): ExamInstitutionSettings
    {
        $this->makeMarkEntryPeriodsObligatory = $makeMarkEntryPeriodsObligatory;
        return $this;
    }

    public function getAutomaticallyRepeatMarksForRepeaters(): ?bool
    {
        return $this->automaticallyRepeatMarksForRepeaters;
    }

    public function setAutomaticallyRepeatMarksForRepeaters(?bool $automaticallyRepeatMarksForRepeaters): ExamInstitutionSettings
    {
        $this->automaticallyRepeatMarksForRepeaters = $automaticallyRepeatMarksForRepeaters;
        return $this;
    }

    public function getAutomateStudentAverages(): ?bool
    {
        return $this->automateStudentAverages;
    }

    public function setAutomateStudentAverages(?bool $automateStudentAverages): ExamInstitutionSettings
    {
        $this->automateStudentAverages = $automateStudentAverages;
        return $this;
    }

    public function getHideNamesIfAnonymityAvailable(): ?bool
    {
        return $this->hideNamesIfAnonymityAvailable;
    }

    public function setHideNamesIfAnonymityAvailable(?bool $hideNamesIfAnonymityAvailable): ExamInstitutionSettings
    {
        $this->hideNamesIfAnonymityAvailable = $hideNamesIfAnonymityAvailable;
        return $this;
    }

    public function getAutomateStudentRanks(): ?bool
    {
        return $this->automateStudentRanks;
    }

    public function setAutomateStudentRanks(?bool $automateStudentRanks): ExamInstitutionSettings
    {
        $this->automateStudentRanks = $automateStudentRanks;
        return $this;
    }

    public function getMultipleTeacherRatings(): ?bool
    {
        return $this->multipleTeacherRatings;
    }

    public function setMultipleTeacherRatings(?bool $multipleTeacherRatings): ExamInstitutionSettings
    {
        $this->multipleTeacherRatings = $multipleTeacherRatings;
        return $this;
    }

    public function getShowPhotoOnSummary(): ?bool
    {
        return $this->showPhotoOnSummary;
    }

    public function setShowPhotoOnSummary(?bool $showPhotoOnSummary): ExamInstitutionSettings
    {
        $this->showPhotoOnSummary = $showPhotoOnSummary;
        return $this;
    }

    public function getEntryBase(): ?float
    {
        return $this->entryBase;
    }

    public function setEntryBase(?float $entryBase): ExamInstitutionSettings
    {
        $this->entryBase = $entryBase;
        return $this;
    }

    public function getIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(?bool $is_enable): ExamInstitutionSettings
    {
        $this->is_enable = $is_enable;
        return $this;
    }

    public function getIsArchive(): ?bool
    {
        return $this->is_archive;
    }

    public function setIsArchive(?bool $is_archive): ExamInstitutionSettings
    {
        $this->is_archive = $is_archive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): ExamInstitutionSettings
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): ExamInstitutionSettings
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getMarkEntryBaseIsCoeff(): ?bool
    {
        return $this->markEntryBaseIsCoeff;
    }

    public function setMarkEntryBaseIsCoeff(?bool $markEntryBaseIsCoeff): ExamInstitutionSettings
    {
        $this->markEntryBaseIsCoeff = $markEntryBaseIsCoeff;
        return $this;
    }

    public function getDisplayNumberOfRowsOnCourses(): ?bool
    {
        return $this->displayNumberOfRowsOnCourses;
    }

    public function setDisplayNumberOfRowsOnCourses(?bool $displayNumberOfRowsOnCourses): void
    {
        $this->displayNumberOfRowsOnCourses = $displayNumberOfRowsOnCourses;
    }

    public function getDisplayTheClassSizeInRows(): ?bool
    {
        return $this->displayTheClassSizeInRows;
    }

    public function setDisplayTheClassSizeInRows(?bool $displayTheClassSizeInRows): void
    {
        $this->displayTheClassSizeInRows = $displayTheClassSizeInRows;
    }
}
