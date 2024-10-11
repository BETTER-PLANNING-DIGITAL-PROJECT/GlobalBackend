<?php

namespace App\State\Provider\School\Study\Program;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Controller\GlobalController;
use App\Entity\School\Study\Program\ClassProgram;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use ReflectionClass;

class ClassProgramProvider implements ProviderInterface
{

    public function __construct(private readonly GlobalController $globalController,private readonly ClassProgramRepository $classProgramRepository,private readonly StudentCourseRegistrationRepository $studentCourseRegistrationRepository)
    {
    }

    function dismount($object) {
        $reflectionClass = new ReflectionClass(get_class($object));
        $array = array();
        foreach ($reflectionClass->getProperties() as $property) {
            $property->setAccessible(true);
            $array[$property->getName()] = $property->getValue($object);
            $property->setAccessible(false);
        }
        return $array;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Retrieve the state from somewhere
        $year = $this->globalController->getUser()->getCurrentYear();
        $classPrograms = $this->classProgramRepository->findBy(['year' => $year],['id'=>'DESC']);
        return array_map(fn(ClassProgram $classProgram) =>  [...$this->dismount($classProgram),'@id' => "/api/class_programs/".$classProgram->getId(),'hasStudentCourseRegistration'=>boolval($this->studentCourseRegistrationRepository->findOneBy(['classProgram'=>$classProgram]))],$classPrograms);
    }
}
