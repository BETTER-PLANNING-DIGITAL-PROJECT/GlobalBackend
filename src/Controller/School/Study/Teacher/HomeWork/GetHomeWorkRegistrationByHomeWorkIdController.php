<?php

namespace App\Controller\School\Study\Teacher\HomeWork;

use App\Repository\School\Study\Teacher\HomeWorkRegistrationRepository;
use App\Repository\School\Study\Teacher\HomeWorkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class GetHomeWorkRegistrationByHomeWorkIdController extends AbstractController
{
    public function __construct(private readonly HomeWorkRegistrationRepository $homeWorkRegistrationRepo, private readonly HomeWorkRepository $homeWorkRepo)
    {
    }

    public function __invoke(Request $request)
    {
        $id = $request->get('id');
        $homeWork = $this->homeWorkRepo->find($id);

        $homeworkRegistrations = $this->homeWorkRegistrationRepo->findBy(['homeWork' => $homeWork]);

        $myHomeworkRegistrations = [];
        foreach ($homeworkRegistrations as $homeworkRegistration){
            $myHomeworkRegistrations [] = [
                '@id' => "/api/home_work_registrations/".$homeworkRegistration->getId(),
                'id' => $homeworkRegistration->getId(),
                'homeWork' => $homeworkRegistration->getHomeWork(),
                'isReceived' => $homeworkRegistration->isIsReceived(),
                'student' => $homeworkRegistration->getStudent() ? $homeworkRegistration->getStudent()->getStudent() : '',
            ];
        }

        return $this->json(['hydra:member' => $myHomeworkRegistrations]);

    }

}



