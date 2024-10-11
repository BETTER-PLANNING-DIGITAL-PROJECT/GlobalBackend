<?php
namespace App\State\Processor\School\Study\Teacher\HomeWork;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\School\Study\Teacher\HomeWorkRegistration;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PublishHomeWorkProcessor implements ProcessorInterface
{
    private Request $request;
    private EntityManagerInterface $manager;
    private StudentCourseRegistrationRepository $studentCourseRegistrationRepo;

    public function __construct(private readonly ProcessorInterface    $processor,
                                Request                                $request,
                                EntityManagerInterface                 $manager,
                                private readonly TokenStorageInterface $tokenStorage,
                                StudentCourseRegistrationRepository    $studentCourseRegistrationRepo) {
        $this->req = $request;
        $this->manager = $manager;
        $this->studentCourseRegistrationRepo = $studentCourseRegistrationRepo;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // $modelData = json_decode($this->req->getContent(), true);

        $studentCourses = $this->studentCourseRegistrationRepo->findAll();

        $data->setIsPublish(true);
        $data->setPublishAt((new \DateTimeImmutable())->setTime(0, 0, 0));

        // Get the teacher's course
        $teacherCourse = $data->getCourse()->getCourse()->getId();

        foreach ($studentCourses as $studentCourse) {
            if ($studentCourse->getClassProgram()->getId() == $teacherCourse) {
                $homeworkRegistration = new HomeWorkRegistration();
                $homeworkRegistration->setStudent($studentCourse->getStudRegistration());
                $homeworkRegistration->setHomeWork($data);

                $homeworkRegistration->setInstitution($this->getUser()->getInstitution());
                $homeworkRegistration->setUser($this->getUser());
                $homeworkRegistration->setYear($this->getUser()->getCurrentYear());

                $this->manager->persist($homeworkRegistration);
            }
        }

        $this->manager->flush();

        return $data;
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
