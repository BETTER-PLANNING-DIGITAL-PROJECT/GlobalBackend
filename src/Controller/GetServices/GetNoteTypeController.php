<?php

namespace App\Controller\GetServices;

use App\Controller\GlobalController;
use App\Repository\School\Exam\Configuration\NoteTypeRepository;
use App\Repository\School\Schooling\Configuration\LevelRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class GetNoteTypeController extends AbstractController
{
    public function __construct(
        private readonly NoteTypeRepository $noteTypeRepository,
        private readonly GlobalController $globalController
    )
    {
    }

    public function __invoke(): array
    {
        $user = $this->globalController->getUser();
        $institution = $user->getInstitution();

        $noteTypes = $this->noteTypeRepository->findBy(['institution' => $institution, 'is_enable' => true]);
        return $noteTypes;
    }
}
