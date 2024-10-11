<?php

namespace App\Controller\School\Exam\Mark;

use App\Repository\School\Exam\Operation\MarkRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MarkEntryController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MarkRepository $markRepository
    )
    {
    }

    #[Route('api/mark/edit/mark/{base}/{weighting}/{assignmentDate}/{description}/{marksObject}', name: 'school_mark_edit_mark')]
    public function editMark(string $base, string $weighting, string $assignmentDate, string $description, string $marksObject): Response
    {
        $marks = json_decode($marksObject, true);
            $assignmentDate = $assignmentDate === 'null' ? null : new DateTimeImmutable($assignmentDate);
            $description = $description === 'null' ? null : $description;
        foreach ($marks as $id => $markEntered) {
            $schoolMark = $this->markRepository->find($id);
            $schoolMark->setBase(floatval($base));
            $schoolMark->setWeighting(floatval($weighting));
            $schoolMark->setAssignmentDate($assignmentDate);
            $schoolMark->setDescription($description);
            // 13 -> 50 , x -> 20 => x = 13 * 20 / 50 = 5,2
            $schoolMark->setMarkEntered($markEntered);
            $mark = $markEntered * 20 / $base;
            $schoolMark->setMark($mark);
        }
        $this->entityManager->flush();
        return $this->json([]);
    }
}
