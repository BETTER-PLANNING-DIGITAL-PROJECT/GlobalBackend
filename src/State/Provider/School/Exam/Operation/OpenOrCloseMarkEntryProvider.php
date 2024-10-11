<?php

namespace App\State\Provider\School\Exam\Operation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Study\Program\ClassProgram;
use App\Repository\School\Exam\Operation\MarkRepository;
use Doctrine\ORM\EntityManagerInterface;

class OpenOrCloseMarkEntryProvider implements ProviderInterface
{
    private ?EntityManagerInterface $entityManager;
    private MarkRepository $markRepository;

    /**
     * @param EntityManagerInterface|null $entityManager
     * @param MarkRepository $markRepository
     */
    public function __construct(?EntityManagerInterface $entityManager, MarkRepository $markRepository)
    {
        $this->entityManager = $entityManager;
        $this->markRepository = $markRepository;
    }


    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Retrieve the state from somewhere
        // Handle the state

        $classProgramRepository = $this->entityManager->getRepository(ClassProgram::class);
        $sequenceRepository = $this->entityManager->getRepository(Sequence::class);
        $classRepository = $this->entityManager->getRepository(SchoolClass::class);
        $evaluationPeriodRepository = $this->entityManager->getRepository(EvaluationPeriod::class);

        // Check on id type
        $sequenceId = $uriVariables['sequenceId'];
        $classId = $uriVariables['classId'];

        $sequence = $sequenceRepository->find($sequenceId);
        $class = $classRepository->find($classId);

        $dql = 'SELECT DISTINCT class_id, sequence_id, evaluation_period_id, class_program_id FROM school_mark s WHERE class_id='.$classId.' AND sequence_id='.$sequenceId;

        if ($uriVariables['evaluationPeriodId'] !== 'null') {
            $dql .= ' AND evaluation_period_id='.$uriVariables['evaluationPeriodId'];
        }
        $conn = $this->entityManager->getConnection();
        $resultSet = $conn->executeQuery($dql);
        $rows = $resultSet->fetchAllAssociative();
        $result = array();

        foreach ($rows as $row) {

            // Recuperation de la course
            $classProgramId = $row['class_program_id'];
            $classProgram = $classProgramRepository->find($classProgramId);

            $evaluationPeriod = $classProgram->getEvaluationPeriod();

            $mark = $this->markRepository->findOneBy([
                'sequence' => $sequence,
                'class' => $class,
                'evaluationPeriod' => $evaluationPeriod,
                'classProgram' => $classProgram
            ]);

            if (isset($mark)) $result[] = ['evaluationPeriodName'=>$evaluationPeriod->getName(),'subjectName'=> $classProgram->getNameuvc(),'id'=>$mark->getId(),'isOpen'=>$mark->isIsOpen()];
        }

        return $result;

    }

}
