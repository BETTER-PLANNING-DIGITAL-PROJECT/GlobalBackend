<?php

namespace App\Controller\School\Exam\Mark;

use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\NoteTypeRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MarkAnonymityController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface     $entityManager,
        private readonly SchoolClassRepository      $classRepository,
        private readonly ClassProgramRepository     $classProgramRepository,
        private readonly SequenceRepository         $sequenceRepository,
        private readonly NoteTypeRepository         $noteTypeRepository,
        private readonly EvaluationPeriodRepository $evaluationPeriodRepository,
        private readonly MarkRepository             $markRepository,
    )
    {
    }

    // Reinitialisation des anonymats
    #[Route('api/mark/anonymity/reset', name: 'school_mark_anonymity_reset')]
    public function reset(Request $request): Response
    {
        $schoolMarkData = json_decode($request->getContent(), true);
        $classId = $schoolMarkData['classId'];
        $classProgramId = $schoolMarkData['classProgramId'];
        $sequenceId = $schoolMarkData['sequenceId'];
        $evaluationPeriodId = $schoolMarkData['evaluationPeriodId'];

        $class = $this->classRepository->find($classId);
        $classProgram = $this->classProgramRepository->find($classProgramId);
        $sequence = $this->sequenceRepository->find($sequenceId);
        $noteType = isset($schoolMarkData['noteTypeId']) && $schoolMarkData['noteTypeId'] !== null ? $this->noteTypeRepository->find($schoolMarkData['noteTypeId']) : null;
        $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);

        $schoolMarks = $this->markRepository->findBy(['class' => $class, 'classProgram' => $classProgram, 'sequence' => $sequence, 'noteType' => $noteType, 'evaluationPeriod' => $evaluationPeriod]);
        foreach ($schoolMarks as $schoolMark) {
            $schoolMark->setAnonymityCode(null);
        }
        $this->entityManager->flush();
        return $this->json([]);
    }

    // Generation des anonymats
    private $formatNumberPossibilities = [
        'A' => 26, 'N' => 10, 'C' => 1
    ];

    private $formatsSelected = [];
    private $lengthsSelected = [];
    private $crushTheOldAnonymity = true;

    function getNumberOfPossibilities()
    {
        $numberOfPossibilities = 1;
        foreach ($this->formatsSelected as $index => $format) {
            $numberOfPossibilities *= pow($this->formatNumberPossibilities[$format], $this->lengthsSelected[$index]);
        }
        return $numberOfPossibilities;
    }

    private $charRangeCodes = [
        'A' => ['min' => 65, 'max' => 90],
        'N' => ['min' => 48, 'max' => 57]
    ];

    private $constantsChar = ['', '', '', '', ''];

    function generateChar(string $format, int $number = 1)
    {
        $min = $this->charRangeCodes[$format]['min'];
        $max = $this->charRangeCodes[$format]['max'];
        $char = "";
        for ($i = 0; $i < $number; $i++) {
            $randomChar = chr(random_int($min, $max));
            $char .= $randomChar;
        }
        return $char;
    }

    function generateRandomChar(int $number)
    {
        $char = '';
        for ($i = 0; $i < $number; $i++) {
            $randomChoice = random_int(0, 1);
            $format = $randomChoice === 0 ? 'A' : 'N';
            $char .= $this->generateChar($format);
        }
        return $char;
    }

    function generateConstantChar(int $number, int $index)
    {
        if (strlen($this->constantsChar[$index]) !== $number) {
            $randomChar = $this->generateRandomChar($number);
            $this->constantsChar[$index] = $randomChar;
            return $randomChar;
        }
        return $this->constantsChar[$index];
    }

    function generateAnonymity()
    {
        $anonymityCode = '';
        foreach ($this->formatsSelected as $index => $format) {
            $number = $this->lengthsSelected[$index];
            $char = $format === "C" ? $this->generateConstantChar($number, $index) : $this->generateChar($format, $number);
            $anonymityCode .= $char;
        }
        return $anonymityCode;
    }

    function generateAnonymityArray(int $number)
    {
        $anonymities = [];
        $anonymity = $this->generateAnonymity();
        while (count($anonymities) < $number) {
            while (in_array($anonymity, $anonymities, true)) {
                $anonymity = $this->generateAnonymity();
            }
            $anonymities[] = $anonymity;
        }
        return $anonymities;
    }

    #[Route('api/mark/anonymity/generate', name: 'school_mark_anonymity_generate')]
    public function generate(Request $request): Response
    {
        $anonymitiesData = json_decode($request->getContent(), true);
        $classId = $anonymitiesData['classId'];

        $classProgram = null;
        if (isset($anonymitiesData['classProgramId'])) {
            $classProgramId = $anonymitiesData['classProgramId'];
            $classProgram = $this->classProgramRepository->find($classProgramId);
        }

        $sequenceId = $anonymitiesData['sequenceId'];

        $this->formatsSelected = $anonymitiesData['formatsSelected'];
        $this->lengthsSelected = $anonymitiesData['lengthsSelected'];
        $this->crushTheOldAnonymity = $anonymitiesData['crushTheOldAnonymity'];
        $currentSubject = $anonymitiesData['currentSubject'];

        $class = $this->classRepository->find($classId);
        $sequence = $this->sequenceRepository->find($sequenceId);
        $noteType = isset($anonymitiesData['noteTypeId']) && $anonymitiesData['noteTypeId'] !== null ? $this->noteTypeRepository->find($anonymitiesData['noteTypeId']) : null;
        $criteriaSchoolMarksByClass = [
            'class' => $class, 'sequence' => $sequence, 'noteType' => $noteType,
        ];

        $evaluationPeriod = null;
        if (isset($anonymitiesData['evaluationPeriodId'])) {
            $evaluationPeriodId = $anonymitiesData['evaluationPeriodId'];
            $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);
            $criteriaSchoolMarksByClass['evaluationPeriod'] = $evaluationPeriod;
        }

        $schoolMarksBySubject = [];
        if ($currentSubject) {
            $criteriaSchoolMarksByClass['classProgram'] = $classProgram;
            $schoolMarksBySubject[$classProgram->getId()] = $this->markRepository->findBy($criteriaSchoolMarksByClass);
        } else {
            $schoolMarksClass = $this->markRepository->findBy($criteriaSchoolMarksByClass);
            foreach ($schoolMarksClass as $schoolMark) {
                $schoolMarksBySubject[$schoolMark->getClassProgram()->getId()][] = $schoolMark;
            }
        }

        if (!$this->crushTheOldAnonymity) {
            foreach ($schoolMarksBySubject as $classProgramId => $schoolMarksSubject) {
                $schoolMarks = [];
                foreach ($schoolMarksSubject as $schoolMark) {
                    if ($schoolMark->getAnonymityCode() === null) {
                        $schoolMarks[] = $schoolMark;
                    }
                }
                $schoolMarksBySubject[$classProgramId] = $schoolMarks;
            }
        }

        $lengths = [];
        foreach ($schoolMarksBySubject as $classProgramId => $schoolMarks) {
            $lengths[] = count($schoolMarks);
        }
        $numberOfAnonymity = $this->getNumberOfPossibilities();
        $isPossible = true;
        $i = 0;
        while ($isPossible && $i < count($lengths)) {
            $isPossible = $isPossible && $numberOfAnonymity > $lengths[$i];
            $i++;
        }
        if (!$isPossible) return $this->json('you cannot generate all anonymities with the format selected');
        $isPossible = array_sum($lengths) > 0;

        if (!$isPossible) return $this->json('there have no anonymity to generate');

        foreach ($schoolMarksBySubject as $classProgramId => $schoolMarksStudents) {
            $numberStudents = count($schoolMarksStudents);

            if ($numberStudents > 0) {

                $anonymityArray = $this->generateAnonymityArray($numberStudents);

                foreach ($schoolMarksStudents as $index => $schoolMark) {
                    $anonymityCode = $anonymityArray[$index];
                    $schoolMark->setAnonymityCode($anonymityCode);
                }
            }
        }
        $this->entityManager->flush();
        return $this->json('ok');
    }
}