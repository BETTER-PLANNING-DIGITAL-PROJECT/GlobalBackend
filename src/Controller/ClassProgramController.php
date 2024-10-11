<?php

namespace App\Controller;

use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelDayCellRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClassProgramController extends AbstractController
{
    public function jsondecode()
    {
        try {
            return file_get_contents('php://input') ?
                json_decode(file_get_contents('php://input'), false) :
                [];
        }catch (\Exception $ex)
        {
            return [];
        }

    }

    private Request $req;
    private EntityManagerInterface $manager;
    private ClassProgramRepository $classProgramRepo;
    private StudentRegistrationRepository $studRegistrationRepo;
    private StudentCourseRegistrationRepository $studCourseRegRepo;
    private TimeTableModelDayCellRepository $timeTableModelDayCellRepo;

    /**
     * @param Request $req
     * @param EntityManagerInterface $manager
     * @param ClassProgramRepository $classProgramRepo
     * @param StudentRegistrationRepository $studRegistrationRepo
     * @param StudentCourseRegistrationRepository $studCourseRegRepo
     */
    public function __construct(Request $req, EntityManagerInterface $manager, ClassProgramRepository $classProgramRepo, StudentRegistrationRepository $studRegistrationRepo, StudentCourseRegistrationRepository $studCourseRegRepo, TimeTableModelDayCellRepository $timeTableModelDayCellRepo)
    {
        $this->req = $req;
        $this->manager = $manager;
        $this->classProgramRepo = $classProgramRepo;
        $this->studRegistrationRepo = $studRegistrationRepo;
        $this->studCourseRegRepo = $studCourseRegRepo;
        $this->timeTableModelDayCellRepo = $timeTableModelDayCellRepo;
    }


    #[Route('/delete/class/program/{id}', name: 'app_class_program_delete')]
    public function delete(int $id): JsonResponse
    {
        $classProgram = $this->classProgramRepo->find($id);
        $hasSchoolMark = $classProgram->getHasSchoolMark();
        if (!$hasSchoolMark){
            $studCourseRegs = $this->studCourseRegRepo->findBy(['classProgram' => $classProgram]);
            foreach ($studCourseRegs as $studCourseReg) $this->manager-> remove($studCourseReg);
            $this->manager-> remove($classProgram);
            $this->manager->flush();
        }
        return $this->json(['deleted'=>!$hasSchoolMark]);
    }

    #[Route('/class/program/edit/{id}', name: 'app_class_program_edit', methods: ['PUT'])]
    public function edit(int $id): JsonResponse
    {
        $classProgram = $this->classProgramRepo->find($id);
        $classProgramData = json_decode($this->req->getContent(), true);
        // Handle the state

        dd($classProgram, $classProgramData);
        $this->manager->flush();
        return $this->json([]);
    }

    #[Route('/api/timetable-model-swap/permute', name: 'model_day_cours_swap', methods: ['POST'])]
    public function SwirchCourses(): JsonResponse
    {
        $data = $this->jsondecode();
        $dayCell1 = $this->timeTableModelDayCellRepo->findOneBy(['id' => $data->daycell1]);
        $dayCell2 = $this->timeTableModelDayCellRepo->findOneBy(['id' => $data->daycell2]);

        $course1 = $dayCell1->getCourse();
        $teacher1 = $dayCell1->getTeacher();

        $dayCell1->setCourse($dayCell2->getCourse());
        $dayCell1->setTeacher($dayCell2->getTeacher());
        
        $dayCell2->setCourse($course1);
        $dayCell2->setTeacher($teacher1);

        $this->manager->persist($dayCell1);
        $this->manager->persist($dayCell2);
        $this->manager->flush();
        return $this->json([]);
    }


}
