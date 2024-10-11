<?php

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class SchoolClassProvider implements ProviderInterface
{
    public function __construct(private readonly SchoolClassRepository $schoolClassRepository,private readonly TokenStorageInterface $tokenStorage
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $classes = $this->schoolClassRepository->findClassWhereCurrentSession($this->getUser()->getCurrentSession());

        foreach ($classes as $classe){
            $myClasses [] = [
                'id' => $classe->getId(),
                'year' => $classe->getYear(),
                'code' => $classe->getCode(),
                'description' => $classe->getDescription(),
                'guardianship' => $classe->getGuardianship(),
                'school' => $classe->getSchool(),
                'department' => $classe->getDepartment(),
                'classCategory' => $classe->getClassCategory(),
                'speciality' => $classe->getSpeciality(),
                'level' => $classe->getLevel(),
                'trainingType' => $classe->getTrainingType(),
                'mainRoom' => $classe->getMainRoom(),
                //'isOptional' => $classe->getYear(),
                'registrantOption' => $classe->getRegistrantOption(),
                'classExam' => $classe->getClassExam(),
                'maximumStudentNumber' => $classe->getMaximumStudentNumber(),
                'ageLimit' => $classe->getAgeLimit(),
                'simpleHourlyRate' => $classe->getSimpleHourlyRate(),
                'multipleHourlyRate' => $classe->getMultipleHourlyRate(),
                'nextClass' => $classe->getNextClass(),
                'isChoiceStudCourseOpen' => $classe->getIsChoiceStudCourseOpen(),
            ];
        }

        return $myClasses;
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
