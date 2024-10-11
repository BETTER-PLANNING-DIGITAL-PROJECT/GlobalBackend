<?php

namespace App\Controller\School\Exam\Mark;

use App\Repository\School\Exam\Operation\MarkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OpenOrCloseMarkEntryController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MarkRepository $markRepository)
    {

    }

    #[Route('api/mark/openOrCloseMarkEntry/{ids}', name: 'school_mark_open_or_close_mark_entry')]
    public function openOrCloseMarkEntry(string $ids): Response
    {
        foreach (explode('-', $ids) as $id) {
            $schoolMark = $this->markRepository->find($id);
            $sequence = $schoolMark->getSequence();
            $class = $schoolMark->getClass();
            $classProgram = $schoolMark->getClassProgram();
            $schoolYear = $schoolMark->getYear();
            $schoolMarks = $this->markRepository->findBy([
                'sequence' => $sequence,
                'class' => $class,
                'classProgram' => $classProgram,
                'year' => $schoolYear
            ]);
            foreach ($schoolMarks as $schoolMarkItem) {
                $schoolMarkItem->setIsOpen(!$schoolMarkItem->isIsOpen());
            }
        }

        $this->entityManager->flush();
        return $this->json([]);
    }
}
