<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\Security\Institution\InstitutionRepository;
use Symfony\Component\HttpFoundation\Request;

class StudCourseRegOpeningClosingProvider implements ProviderInterface
{
    private Request $request;
    private StudentCourseRegistrationRepository $studCourseRegRepository;
    private InstitutionRepository $institutionRepository;
    private SchoolRepository $schoolRepository;
    // private YearRepository $yearRepository;
    private SchoolClassRepository $schoolClassRepository;

    private StudentRegistrationRepository $studregistrationRepository;

    /**
     * @param Request $request
     * @param StudentCourseRegistrationRepository $studCourseRegRepository
     * @param InstitutionRepository $institutionRepository
     * @param SchoolRepository $schoolRepository
     * @param SchoolClassRepository $schoolClassRepository
     * @param StudentRegistrationRepository $studregistrationRepository
     */
    public function __construct(Request $request, StudentCourseRegistrationRepository $studCourseRegRepository, InstitutionRepository $institutionRepository, SchoolRepository $schoolRepository, SchoolClassRepository $schoolClassRepository, StudentRegistrationRepository $studregistrationRepository)
    {
        $this->request = $request;
        $this->studCourseRegRepository = $studCourseRegRepository;
        $this->institutionRepository = $institutionRepository;
        $this->schoolRepository = $schoolRepository;
        // $this->yearRepository = $yearRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->studregistrationRepository = $studregistrationRepository;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Retrieve the state from somewhere
//        $params = json_decode($this->request->getContent(),true);
        $institution = $this->institutionRepository->find(1);
//        return $this->studCourseRegRepository->findBy(['name'=>$name]);
        $classes = $this->studregistrationRepository->findClasses($institution);
        $studCourseRegs = array();
        foreach ($classes as $classArray) {
            $yearId = $classArray['1'];
            // $year = $this->yearRepository->find($yearId);
            $schoolId = $classArray['2'];
            $school = $this->schoolRepository->find($schoolId);
            $classId = $classArray['3'];
            $class = $this->schoolClassRepository->find($classId);
            $studCourseReg = $this->studCourseRegRepository->findOneBy(['year'=>$year,'school'=>$school,'class'=>$class,'institution'=>$institution]);
            if (isset($studCourseReg)) $studCourseRegs[] = $studCourseReg;
        }
//        dd($studCourseRegs);
        return $studCourseRegs;
    }
}
