<?php

namespace App\State\Processor\School\Exam\Configuration;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\MarkGradeRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PutSchoolMarkGradeProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface     $processor,
                                private readonly TokenStorageInterface  $tokenStorage,
                                private readonly MarkGradeRepository    $markGradeRepository)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $user = $this->getUser();
        $institution = $user->getInstitution();
        $currentYear = $user->getCurrentYear();

        // Handle the state
        $school = $data->getSchool();
        $markGrade = $this->markGradeRepository->findOneBy(['code' => $data->getCode(), 'school' => $school, 'year' => $currentYear]);
        if ($markGrade && $markGrade->getId() !== $data->getId()) return new JsonResponse('code');
        $markGrades = $this->markGradeRepository->findBy(['school' => $school, 'year' => $currentYear]);
        $min = $data->getMin();
        $max = $data->getMax();
        foreach ($markGrades as $markGrade) {
            if ($markGrade->getId() === $data->getId()) continue;
            $markGradeMin = $markGrade->getMin();
            $markGradeMax = $markGrade->getMax();
            if (!(($min <= $markGradeMin && $max <= $markGradeMin) || ($min >= $markGradeMax && $max >= $markGradeMax))) return new JsonResponse('range');
        }
        $data->setInstitution($institution);
        $data->setYear($currentYear);
        return $this->processor->process($data, $operation, $uriVariables, $context);
    }


    public function getUser(): ?User
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }
}
