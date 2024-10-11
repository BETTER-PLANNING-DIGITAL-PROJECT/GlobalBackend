<?php

namespace App\Controller\School\Study\Program;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetClassProgramCollectionController extends AbstractController
{
    private ClassProgramRepository $classProgramRepository;
    private SystemSettingsRepository $systemSettingsRepository;
    private SchoolRepository $schoolRepository;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                ClassProgramRepository                     $classProgramRepository,
                                SystemSettingsRepository                     $systemSettingsRepository,
                                SchoolRepository                     $schoolRepository,
    )
    {
        $this->classProgramRepository = $classProgramRepository;
        $this->systemSettingsRepository = $systemSettingsRepository;
        $this->schoolRepository = $schoolRepository;
    }

    public function __invoke(Request $request):JsonResponse
    {
        $requestData = [];

        if($this->getUser()->isIsBranchManager()){
            $classPrograms = $this->classProgramRepository->findBy([], ['id' => 'DESC']);

            foreach ($classPrograms as $classProgram)
            {
                $requestData[] = [
                    '@id' => "/api/get/class-program/" . $classProgram->getId(),
                    '@type' => "ClassProgram",
                    'id'=> $classProgram ->getId(),
                    'codeuvc'=> $classProgram->getCodeuvc(),
                    'nameuvc'=> $classProgram->getNameuvc(),
                    'position'=> $classProgram->getPosition(),
                    'coeff'=> $classProgram->getCoeff(),
                    'validationBase'=> $classProgram->getValidationBase(),
                    'year' => [
                        '@id' => "/api/get/year/" . ($classProgram->getYear() ? $classProgram->getYear()->getId() : ''),
                        '@type' => "Year",
                        'id' => $classProgram->getYear() ? $classProgram->getYear()->getId() : '',
                        'year' => $classProgram->getYear() ? $classProgram->getYear()->getYear() : '',
                    ],
                    'subject' => [
                        '@id' => "/api/get/subject/" . ($classProgram->getSubject() ? $classProgram->getSubject()->getId() : ''),
                        '@type' => "Subject",
                        'id' => $classProgram->getSubject() ? $classProgram->getSubject()->getId() : '',
                        'name' => $classProgram->getSubject() ? $classProgram->getSubject()->getName() : '',
                    ],
                    'class' => [
                        '@id' => "/api/get/class/" . ($classProgram->getClass() ? $classProgram->getClass()->getId() : ''),
                        '@type' => "Class",
                        'id' => $classProgram->getClass() ? $classProgram->getClass()->getId() : '',
                        'code' => $classProgram->getClass() ? $classProgram->getClass()->getCode() : '',
                    ],
                    'evaluationPeriod' => [
                        '@id' => "/api/get/evaluation-period/" . ($classProgram->getEvaluationPeriod() ? $classProgram->getEvaluationPeriod()->getId() : ''),
                        '@type' => "EvaluationPeriod",
                        'id' => $classProgram->getEvaluationPeriod() ? $classProgram->getEvaluationPeriod()->getId() : '',
                        'name' => $classProgram->getEvaluationPeriod() ? $classProgram->getEvaluationPeriod()->getName() : '',
                    ],
                    'school' => [
                        '@id' => "/api/get/school/" . ($classProgram->getSchool() ? $classProgram->getSchool()->getId() : ''),
                        '@type' => "School",
                        'id' => $classProgram->getSchool() ? $classProgram->getSchool()->getId() : '',
                        'name' => $classProgram->getSchool() ? $classProgram->getSchool()->getName() : '',
                    ],
                    'module' => [
                        '@id' => "/api/get/course-module/" . ($classProgram->getModule() ? $classProgram->getModule()->getId() : ''),
                        '@type' => "Module",
                        'id' => $classProgram->getModule() ? $classProgram->getModule()->getId() : '',
                        'name' => $classProgram->getModule() ? $classProgram->getModule()->getName() : '',
                    ],
                    'nature' => [
                        '@id' => "/api/get/subject-nature/" . ($classProgram->getNature() ? $classProgram->getNature()->getId() : ''),
                        '@type' => "SubjectNature",
                        'id' => $classProgram->getNature() ? $classProgram->getNature()->getId() : '',
                        'name' => $classProgram->getNature() ? $classProgram->getNature()->getName() : '',
                    ],
                    'principalRoom' => [
                        '@id' => "/api/get/room/" . ($classProgram->getPrincipalRoom() ? $classProgram->getPrincipalRoom()->getId() : ''),
                        '@type' => "Room",
                        'id' => $classProgram->getPrincipalRoom() ? $classProgram->getPrincipalRoom()->getId() : '',
                        'name' => $classProgram->getPrincipalRoom() ? $classProgram->getPrincipalRoom()->getName() : '',
                    ],
                    'branch' => [
                        '@id' => "/api/get/branch/" . ($classProgram->getBranch() ? $classProgram->getBranch()->getId() : ''),
                        '@type' => "Branch",
                        'id' => $classProgram->getBranch() ? $classProgram->getBranch()->getId() : '',
                        'code' => $classProgram->getBranch() ? $classProgram->getBranch()->getCode() : '',
                        'name' => $classProgram->getBranch() ? $classProgram->getBranch()->getName() : '',
                    ],
                ];
            }
        }
        else
        {
            $systemSettings = $this->systemSettingsRepository->findOneBy([]);
            if($systemSettings)
            {
                if($systemSettings->isIsBranches())
               {
                   $userBranches = $this->getUser()->getUserBranches();
                   foreach ($userBranches as $userBranch) {

                       $classPrograms = $this->classProgramRepository->findBy(['branch' => $userBranch], ['id' => 'DESC']);
                          foreach ($classPrograms as $classProgram){
                              $requestData[] = [
                                  '@id' => "/api/get/class-program/" . $classProgram->getId(),
                                  '@type' => "ClassProgram",
                                  'id'=> $classProgram ->getId(),
                                  'codeuvc'=> $classProgram->getCodeuvc(),
                                  'nameuvc'=> $classProgram->getNameuvc(),
                                  'position'=> $classProgram->getPosition(),
                                  'coeff'=> $classProgram->getCoeff(),
                                  'validationBase'=> $classProgram->getValidationBase(),
                                  'year' => [
                                      '@id' => "/api/get/year/" . ($classProgram->getYear() ? $classProgram->getYear()->getId() : ''),
                                      '@type' => "Year",
                                      'id' => $classProgram->getYear() ? $classProgram->getYear()->getId() : '',
                                      'year' => $classProgram->getYear() ? $classProgram->getYear()->getYear() : '',
                                  ],
                                  'subject' => [
                                      '@id' => "/api/get/subject/" . ($classProgram->getSubject() ? $classProgram->getSubject()->getId() : ''),
                                      '@type' => "Subject",
                                      'id' => $classProgram->getSubject() ? $classProgram->getSubject()->getId() : '',
                                      'name' => $classProgram->getSubject() ? $classProgram->getSubject()->getName() : '',
                                  ],
                                  'class' => [
                                      '@id' => "/api/get/class/" . ($classProgram->getClass() ? $classProgram->getClass()->getId() : ''),
                                      '@type' => "Class",
                                      'id' => $classProgram->getClass() ? $classProgram->getClass()->getId() : '',
                                      'code' => $classProgram->getClass() ? $classProgram->getClass()->getCode() : '',
                                  ],
                                  'evaluationPeriod' => [
                                      '@id' => "/api/get/evaluation-period/" . ($classProgram->getEvaluationPeriod() ? $classProgram->getEvaluationPeriod()->getId() : ''),
                                      '@type' => "EvaluationPeriod",
                                      'id' => $classProgram->getEvaluationPeriod() ? $classProgram->getEvaluationPeriod()->getId() : '',
                                      'name' => $classProgram->getEvaluationPeriod() ? $classProgram->getEvaluationPeriod()->getName() : '',
                                  ],
                                  'school' => [
                                      '@id' => "/api/get/school/" . ($classProgram->getSchool() ? $classProgram->getSchool()->getId() : ''),
                                      '@type' => "School",
                                      'id' => $classProgram->getSchool() ? $classProgram->getSchool()->getId() : '',
                                      'name' => $classProgram->getSchool() ? $classProgram->getSchool()->getName() : '',
                                  ],
                                  'module' => [
                                      '@id' => "/api/get/course-module/" . ($classProgram->getModule() ? $classProgram->getModule()->getId() : ''),
                                      '@type' => "Module",
                                      'id' => $classProgram->getModule() ? $classProgram->getModule()->getId() : '',
                                      'name' => $classProgram->getModule() ? $classProgram->getModule()->getName() : '',
                                  ],
                                  'nature' => [
                                      '@id' => "/api/get/subject-nature/" . ($classProgram->getNature() ? $classProgram->getNature()->getId() : ''),
                                      '@type' => "SubjectNature",
                                      'id' => $classProgram->getNature() ? $classProgram->getNature()->getId() : '',
                                      'name' => $classProgram->getNature() ? $classProgram->getNature()->getName() : '',
                                  ],
                                  'principalRoom' => [
                                      '@id' => "/api/get/room/" . ($classProgram->getPrincipalRoom() ? $classProgram->getPrincipalRoom()->getId() : ''),
                                      '@type' => "Room",
                                      'id' => $classProgram->getPrincipalRoom() ? $classProgram->getPrincipalRoom()->getId() : '',
                                      'name' => $classProgram->getPrincipalRoom() ? $classProgram->getPrincipalRoom()->getName() : '',
                                  ],
                                  'branch' => [
                                      '@id' => "/api/get/branch/" . ($classProgram->getBranch() ? $classProgram->getBranch()->getId() : ''),
                                      '@type' => "Branch",
                                      'id' => $classProgram->getBranch() ? $classProgram->getBranch()->getId() : '',
                                      'code' => $classProgram->getBranch() ? $classProgram->getBranch()->getCode() : '',
                                      'name' => $classProgram->getBranch() ? $classProgram->getBranch()->getName() : '',
                                  ],
                              ];
                          }
                       }
               }
               else {
                   $school = $this->schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
                   if($school) {
                       $classPrograms = $this->classProgramRepository->findBy(['school' => $school], ['id' => 'DESC']);

                       foreach ($classPrograms as $classProgram) {
                           if ($classProgram) {
                               $requestData[] = [
                                   '@id' => "/api/get/class-program/" . $classProgram->getId(),
                                   '@type' => "ClassProgram",
                                   'id'=> $classProgram ->getId(),
                                   'codeuvc'=> $classProgram->getCodeuvc(),
                                   'nameuvc'=> $classProgram->getNameuvc(),
                                   'position'=> $classProgram->getPosition(),
                                   'coeff'=> $classProgram->getCoeff(),
                                   'validationBase'=> $classProgram->getValidationBase(),
                                   'year' => [
                                       '@id' => "/api/get/year/" . ($classProgram->getYear() ? $classProgram->getYear()->getId() : ''),
                                       '@type' => "Year",
                                       'id' => $classProgram->getYear() ? $classProgram->getYear()->getId() : '',
                                       'year' => $classProgram->getYear() ? $classProgram->getYear()->getYear() : '',
                                   ],
                                   'subject' => [
                                       '@id' => "/api/get/subject/" . ($classProgram->getSubject() ? $classProgram->getSubject()->getId() : ''),
                                       '@type' => "Subject",
                                       'id' => $classProgram->getSubject() ? $classProgram->getSubject()->getId() : '',
                                       'name' => $classProgram->getSubject() ? $classProgram->getSubject()->getName() : '',
                                   ],
                                   'class' => [
                                       '@id' => "/api/get/class/" . ($classProgram->getClass() ? $classProgram->getClass()->getId() : ''),
                                       '@type' => "Class",
                                       'id' => $classProgram->getClass() ? $classProgram->getClass()->getId() : '',
                                       'code' => $classProgram->getClass() ? $classProgram->getClass()->getCode() : '',
                                   ],
                                   'evaluationPeriod' => [
                                       '@id' => "/api/get/evaluation-period/" . ($classProgram->getEvaluationPeriod() ? $classProgram->getEvaluationPeriod()->getId() : ''),
                                       '@type' => "EvaluationPeriod",
                                       'id' => $classProgram->getEvaluationPeriod() ? $classProgram->getEvaluationPeriod()->getId() : '',
                                       'name' => $classProgram->getEvaluationPeriod() ? $classProgram->getEvaluationPeriod()->getName() : '',
                                   ],
                                   'school' => [
                                       '@id' => "/api/get/school/" . ($classProgram->getSchool() ? $classProgram->getSchool()->getId() : ''),
                                       '@type' => "School",
                                       'id' => $classProgram->getSchool() ? $classProgram->getSchool()->getId() : '',
                                       'name' => $classProgram->getSchool() ? $classProgram->getSchool()->getName() : '',
                                   ],
                                   'module' => [
                                       '@id' => "/api/get/course-module/" . ($classProgram->getModule() ? $classProgram->getModule()->getId() : ''),
                                       '@type' => "Module",
                                       'id' => $classProgram->getModule() ? $classProgram->getModule()->getId() : '',
                                       'name' => $classProgram->getModule() ? $classProgram->getModule()->getName() : '',
                                   ],
                                   'nature' => [
                                       '@id' => "/api/get/subject-nature/" . ($classProgram->getNature() ? $classProgram->getNature()->getId() : ''),
                                       '@type' => "SubjectNature",
                                       'id' => $classProgram->getNature() ? $classProgram->getNature()->getId() : '',
                                       'name' => $classProgram->getNature() ? $classProgram->getNature()->getName() : '',
                                   ],
                                   'principalRoom' => [
                                       '@id' => "/api/get/room/" . ($classProgram->getPrincipalRoom() ? $classProgram->getPrincipalRoom()->getId() : ''),
                                       '@type' => "Room",
                                       'id' => $classProgram->getPrincipalRoom() ? $classProgram->getPrincipalRoom()->getId() : '',
                                       'name' => $classProgram->getPrincipalRoom() ? $classProgram->getPrincipalRoom()->getName() : '',
                                   ],
                                   'branch' => [
                                       '@id' => "/api/get/branch/" . ($classProgram->getBranch() ? $classProgram->getBranch()->getId() : ''),
                                       '@type' => "Branch",
                                       'id' => $classProgram->getBranch() ? $classProgram->getBranch()->getId() : '',
                                       'code' => $classProgram->getBranch() ? $classProgram->getBranch()->getCode() : '',
                                       'name' => $classProgram->getBranch() ? $classProgram->getBranch()->getName() : '',
                                   ],
                               ];
                           }
                       }

                   }
               }
            }
        }


        return $this->json(['hydra:member' => $requestData]);
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
