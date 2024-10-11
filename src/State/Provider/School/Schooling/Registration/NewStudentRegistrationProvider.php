<?php

namespace App\State\Provider\School\Schooling\Registration;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class NewStudentRegistrationProvider implements ProviderInterface
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly StudentRegistrationRepository $studentRegistrationRepository,
                                private readonly StudentCourseRegistrationRepository $studentCourseRegistrationRepository,
                                private readonly SystemSettingsRepository $systemSettingsRepository,
                                private readonly SchoolRepository $schoolRepository,
                                Request $request){
        $this->req = $request;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $currentYear = $this->getUser()->getCurrentYear();
        $userInstitution = $this->getUser()->getInstitution();
        $newStudentRegistrations = [];

        if($this->getUser()->isIsBranchManager()){
            $studentRegistrations = $this->studentRegistrationRepository->findNewStudentRegistration($currentYear, $userInstitution);

            foreach ($studentRegistrations as $studentRegistration){
                $status = $studentRegistration->getStudent()->getStatus();
                if($status == 'dismissed' || $status == 'resigned') {
                    continue;
                }

                $newStudentRegistrations [] = [
                    'id' => $studentRegistration->getId(),
                    'speciality' => $studentRegistration->getSpeciality(),
                    'center' => $studentRegistration->getCenter(),
                    'pvdiplome' => $studentRegistration->getPvdiplome(),
                    'pvselection' => $studentRegistration->getPvselection(),
                    'ranks' => $studentRegistration->getRanks(),
                    'repeating' => $studentRegistration->getRepeating(),
                    'elementsprovided' => $studentRegistration->getElementsProvided(),
                    'average' => $studentRegistration->getAverage(),
                    'registrationdate' => $studentRegistration->getRegistrationDate() ? $studentRegistration->getRegistrationDate()->format("Y-m-d") : "",
                    'diploma' => $studentRegistration->getDiploma(),
                    'school' => $studentRegistration->getSchool(),
                    'cycle' => $studentRegistration->getCycle(),
                    'level' => $studentRegistration->getLevel(),
                    'regime' => $studentRegistration->getRegime(),
                    'options' => $studentRegistration->getOptions(),
                    'classe' => $studentRegistration->getClasse(),
                    'transactions' => $studentRegistration->getTransactions(),
                    'status' => $studentRegistration->getStatus(),
                    'hasStudentCourseRegistration' => boolval($this->studentCourseRegistrationRepository->findOneBy(['StudRegistration' => $studentRegistration])),
                    'student' => $studentRegistration->getStudent(),
                    'year' => $studentRegistration->getYear()->getYear(),

                    'sex' => $studentRegistration->getStudent() ? $studentRegistration->getStudent()->getSex() : '',
                    'country' => $studentRegistration->getStudent() ? $studentRegistration->getStudent()->getCountry() : '',
                    'religion' => $studentRegistration->getStudent() ? $studentRegistration->getStudent()->getReligion() : '',
                    'rhesus' => $studentRegistration->getStudent() ? $studentRegistration->getStudent()->getRhesus() : '',
                    'bloodGroup' => $studentRegistration->getStudent() ? $studentRegistration->getStudent()->getBloodGroup() : '',
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
                            $studentRegistrations =$this->studentRegistrationRepository->findBy(['school' => $school], ['id' => 'DESC']);

                            foreach ($studentRegistrations as $studentRegistration){

                                $status = $studentRegistration->getStudent()->getStatus();
                                if($status == 'dismissed' || $status == 'resigned') {
                                    continue;
                                }
                                $newStudentRegistrations [] = [
                                    'id' => $studentRegistration->getId(),
                                    'speciality' => $studentRegistration->getSpeciality(),
                                    'center' => $studentRegistration->getCenter(),
                                    'pvdiplome' => $studentRegistration->getPvdiplome(),
                                    'pvselection' => $studentRegistration->getPvselection(),
                                    'ranks' => $studentRegistration->getRanks(),
                                    'repeating' => $studentRegistration->getRepeating(),
                                    'elementsprovided' => $studentRegistration->getElementsProvided(),
                                    'average' => $studentRegistration->getAverage(),
                                    'registrationdate' => $studentRegistration->getRegistrationDate() ? $studentRegistration->getRegistrationDate()->format("Y-m-d") : "",
                                    'diploma' => $studentRegistration->getDiploma(),
                                    'school' => $studentRegistration->getSchool(),
                                    'cycle' => $studentRegistration->getCycle(),
                                    'level' => $studentRegistration->getLevel(),
                                    'regime' => $studentRegistration->getRegime(),
                                    'options' => $studentRegistration->getOptions(),
                                    'classe' => $studentRegistration->getClasse(),
                                    'transactions' => $studentRegistration->getTransactions(),
                                    'status' => $studentRegistration->getStatus(),
                                    'hasStudentCourseRegistration' => boolval($this->studentCourseRegistrationRepository->findOneBy(['StudRegistration' => $studentRegistration])),
                                    'student' => $studentRegistration->getStudent(),
                                    'year' => $studentRegistration->getYear()->getYear(),

                                    'sex' => $studentRegistration->getStudent() ? $studentRegistration->getStudent()->getSex() : '',
                                    'country' => $studentRegistration->getStudent() ? $studentRegistration->getStudent()->getCountry() : '',
                                    'religion' => $studentRegistration->getStudent() ? $studentRegistration->getStudent()->getReligion() : '',
                                    'rhesus' => $studentRegistration->getStudent() ? $studentRegistration->getStudent()->getRhesus() : '',
                                    'bloodGroup' => $studentRegistration->getStudent() ? $studentRegistration->getStudent()->getBloodGroup() : '',
                                ];
                            }
                        }
                    }
                }
                else {
                    $school = $this->schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
                    if($school) {
                        $studentRegistrations = $this->studentRegistrationRepository->findBy(['school' => $school], ['id' => 'DESC']);

                        foreach ($studentRegistrations as $studentRegistration) {
                            if ($studentRegistration) {
                                $status = $studentRegistration->getStudent()->getStatus();
                                if($status == 'dismissed' || $status == 'resigned') {
                                    continue;
                                }
                                $newStudentRegistrations [] = [
                                    'id' => $studentRegistration->getId(),
                                    'speciality' => $studentRegistration->getSpeciality(),
                                    'center' => $studentRegistration->getCenter(),
                                    'pvdiplome' => $studentRegistration->getPvdiplome(),
                                    'pvselection' => $studentRegistration->getPvselection(),
                                    'ranks' => $studentRegistration->getRanks(),
                                    'repeating' => $studentRegistration->getRepeating(),
                                    'elementsprovided' => $studentRegistration->getElementsProvided(),
                                    'average' => $studentRegistration->getAverage(),
                                    'registrationdate' => $studentRegistration->getRegistrationDate() ? $studentRegistration->getRegistrationDate()->format("Y-m-d") : "",
                                    'diploma' => $studentRegistration->getDiploma(),
                                    'school' => $studentRegistration->getSchool(),
                                    'cycle' => $studentRegistration->getCycle(),
                                    'level' => $studentRegistration->getLevel(),
                                    'regime' => $studentRegistration->getRegime(),
                                    'options' => $studentRegistration->getOptions(),
                                    'classe' => $studentRegistration->getClasse(),
                                    'transactions' => $studentRegistration->getTransactions(),
                                    'status' => $studentRegistration->getStatus(),
                                    'hasStudentCourseRegistration' => boolval($this->studentCourseRegistrationRepository->findOneBy(['StudRegistration' => $studentRegistration])),
                                    'student' => $studentRegistration->getStudent(),
                                    'year' => $studentRegistration->getYear()->getYear(),

                                    'sex' => $studentRegistration->getStudent() ? $studentRegistration->getStudent()->getSex() : '',
                                    'country' => $studentRegistration->getStudent() ? $studentRegistration->getStudent()->getCountry() : '',
                                    'religion' => $studentRegistration->getStudent() ? $studentRegistration->getStudent()->getReligion() : '',
                                    'rhesus' => $studentRegistration->getStudent() ? $studentRegistration->getStudent()->getRhesus() : '',
                                    'bloodGroup' => $studentRegistration->getStudent() ? $studentRegistration->getStudent()->getBloodGroup() : '',
                                ];
                            }
                        }
                    }

                }
            }
        }

        return array(['newStudentRegistration' => $newStudentRegistrations]);
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
