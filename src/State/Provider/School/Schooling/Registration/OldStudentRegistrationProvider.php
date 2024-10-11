<?php

namespace App\State\Provider\School\Schooling\Registration;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class OldStudentRegistrationProvider implements ProviderInterface
{
    public function __construct(private readonly StudentRegistrationRepository $studentRegistrationRepository,
                                private readonly StudentCourseRegistrationRepository $studentCourseRegistrationRepository,
                                private readonly SystemSettingsRepository $systemSettingsRepository,
                                private readonly SchoolRepository $schoolRepository,
                                private readonly TokenStorageInterface $tokenStorage, )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $currentYear = $this->getUser()->getCurrentYear();
        $userInstitution = $this->getUser()->getInstitution();
        $myStudentRegistrations = [];

        if($this->getUser()->isIsBranchManager()){
            $studentRegistrations = $this->studentRegistrationRepository->findOldStudentRegistration($currentYear, $userInstitution);

            foreach ($studentRegistrations as $studentRegistration){
                $status = $studentRegistration->getStudent()->getStatus();
                if($status == 'dismissed' || $status == 'resigned') {
                    continue;
                }

                $myStudentRegistrations [] = [
                    'id' => $studentRegistration->getId(),
                    'speciality' => $studentRegistration->getSpeciality(),
                    'center' => $studentRegistration->getCenter(),
                    'pvdiplome' => $studentRegistration->getPvdiplome(),
                    'pvselection' => $studentRegistration->getPvselection(),
                    'ranks' => $studentRegistration->getRanks(),
                    'repeating' => $studentRegistration->getRepeating(),
                    'elementsprovided' => $studentRegistration->getElementsProvided(),
                    'average' => $studentRegistration->getAverage(),
                    'registrationdate' => $studentRegistration->getRegistrationDate() ? $studentRegistration->getRegistrationDate()->format("Y-m-d") : '',
                    'diploma' => $studentRegistration->getDiploma(),
                    'status' => $studentRegistration->getStatus(),
                    'school' => $studentRegistration->getSchool(),
                    'regime' => $studentRegistration->getRegime(),
                    'options' => $studentRegistration->getOptions(),
                    'classe' => $studentRegistration->getClasse(),
                    'currentClass' => $studentRegistration->getCurrentClass(),
                    'year' => $studentRegistration->getYear(),
                    'currentYear' => $studentRegistration->getCurrentYear(),
                    'transactions' => $studentRegistration->getTransactions(),
                    'studentRegistration' => $studentRegistration->getStudentRegistration(),
                    'student' => $studentRegistration->getStudentRegistration()->getStudent(),
                    'hasStudentCourseRegistration' => boolval($this->studentCourseRegistrationRepository->findOneBy(['StudRegistration' => $studentRegistration])),
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
                        $school = $this->schoolRepository->findOneBy(['schoolBranch' => $userBranch]);
                        if ($school) {
                            $studentRegistrations = $this->studentRegistrationRepository->findOldStudentRegistrationSchool($currentYear, $userInstitution, $school);

                            foreach ($studentRegistrations as $studentRegistration){
                                $status = $studentRegistration->getStudent()->getStatus();
                                if($status == 'dismissed' || $status == 'resigned') {
                                    continue;
                                }

                                $myStudentRegistrations [] = [
                                    'id' => $studentRegistration->getId(),
                                    'speciality' => $studentRegistration->getSpeciality(),
                                    'center' => $studentRegistration->getCenter(),
                                    'pvdiplome' => $studentRegistration->getPvdiplome(),
                                    'pvselection' => $studentRegistration->getPvselection(),
                                    'ranks' => $studentRegistration->getRanks(),
                                    'repeating' => $studentRegistration->getRepeating(),
                                    'elementsprovided' => $studentRegistration->getElementsProvided(),
                                    'average' => $studentRegistration->getAverage(),
                                    'registrationdate' => $studentRegistration->getRegistrationDate() ? $studentRegistration->getRegistrationDate()->format("Y-m-d") : '',
                                    'diploma' => $studentRegistration->getDiploma(),
                                    'status' => $studentRegistration->getStatus(),
                                    'school' => $studentRegistration->getSchool(),
                                    'regime' => $studentRegistration->getRegime(),
                                    'options' => $studentRegistration->getOptions(),
                                    'classe' => $studentRegistration->getClasse(),
                                    'currentClass' => $studentRegistration->getCurrentClass(),
                                    'year' => $studentRegistration->getYear(),
                                    'currentYear' => $studentRegistration->getCurrentYear(),
                                    'transactions' => $studentRegistration->getTransactions(),
                                    'studentRegistration' => $studentRegistration->getStudentRegistration(),
                                    'student' => $studentRegistration->getStudentRegistration()->getStudent(),
                                    'hasStudentCourseRegistration' => boolval($this->studentCourseRegistrationRepository->findOneBy(['StudRegistration' => $studentRegistration])),
                                ];
                            }
                        }
                    }
                }
                else {
                    $school = $this->schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
                    if($school) {
                        $studentRegistrations = $this->studentRegistrationRepository->findOldStudentRegistrationSchool($currentYear, $userInstitution, $school);

                        foreach ($studentRegistrations as $studentRegistration) {
                            if ($studentRegistration) {
                                $status = $studentRegistration->getStudent()->getStatus();
                                if($status == 'dismissed' || $status == 'resigned') {
                                    continue;
                                }

                                $myStudentRegistrations [] = [
                                    'id' => $studentRegistration->getId(),
                                    'speciality' => $studentRegistration->getSpeciality(),
                                    'center' => $studentRegistration->getCenter(),
                                    'pvdiplome' => $studentRegistration->getPvdiplome(),
                                    'pvselection' => $studentRegistration->getPvselection(),
                                    'ranks' => $studentRegistration->getRanks(),
                                    'repeating' => $studentRegistration->getRepeating(),
                                    'elementsprovided' => $studentRegistration->getElementsProvided(),
                                    'average' => $studentRegistration->getAverage(),
                                    'registrationdate' => $studentRegistration->getRegistrationDate() ? $studentRegistration->getRegistrationDate()->format("Y-m-d") : '',
                                    'diploma' => $studentRegistration->getDiploma(),
                                    'status' => $studentRegistration->getStatus(),
                                    'school' => $studentRegistration->getSchool(),
                                    'regime' => $studentRegistration->getRegime(),
                                    'options' => $studentRegistration->getOptions(),
                                    'classe' => $studentRegistration->getClasse(),
                                    'currentClass' => $studentRegistration->getCurrentClass(),
                                    'year' => $studentRegistration->getYear(),
                                    'currentYear' => $studentRegistration->getCurrentYear(),
                                    'transactions' => $studentRegistration->getTransactions(),
                                    'studentRegistration' => $studentRegistration->getStudentRegistration(),
                                    'student' => $studentRegistration->getStudentRegistration()->getStudent(),
                                    'hasStudentCourseRegistration' => boolval($this->studentCourseRegistrationRepository->findOneBy(['StudRegistration' => $studentRegistration])),
                                ];
                            }
                        }
                    }

                }
            }
        }

        return array(['studentRegistration' => $myStudentRegistrations]);
    }


    private function serializeSearchSTR(StudentRegistration $studentRegistration): array
    {
        $studentRegistrations = $this->studentRegistrationRepository->findBy(['studentRegistration' => $studentRegistration]);

        $myStudentRegistrations = [];

        foreach ($studentRegistrations as $studentRegistration)
        {
            $myStudentRegistrations[] = [
                'id' => $studentRegistration->getId(),
                'name' => $studentRegistration->getStudent()->getName(),
            ];
        }

        return $myStudentRegistrations;
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

