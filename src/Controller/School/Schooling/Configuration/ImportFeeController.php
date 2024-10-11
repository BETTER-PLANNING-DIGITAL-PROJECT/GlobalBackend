<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Entity\School\Schooling\Configuration\Fee;
use App\Repository\Budget\BudgetLineRepository;
use App\Repository\School\Schooling\Configuration\CostAreaRepository;
use App\Repository\School\Schooling\Configuration\CycleRepository;
use App\Repository\School\Schooling\Configuration\FeeRepository;
use App\Repository\School\Schooling\Configuration\LevelRepository;
use App\Repository\School\Schooling\Configuration\PensionSchemeRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Configuration\SpecialityRepository;
use App\Repository\School\Schooling\Configuration\TrainingTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class ImportFeeController extends AbstractController
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

    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(Request $request,
                             FeeRepository $feeRepository,
                             SchoolRepository $schoolRepository,
                             CostAreaRepository $costAreaRepository,
                             SchoolClassRepository $schoolClassRepository,
                             PensionSchemeRepository $pensionSchemeRepository,
                             CycleRepository $cycleRepository,
                             SpecialityRepository $specialityRepository,
                             LevelRepository $levelRepository,
                             TrainingTypeRepository $trainingTypeRepository,
                             BudgetLineRepository $budgetLineRepository): Response
    {
        $data = $this->jsondecode();

        if(!isset($data->data))
            return throw new BadRequestHttpException('file is compulsory');

        $fees = $data->data;

        if (!$fees) {
            throw new BadRequestHttpException('"file" is required');
        }

        foreach ($fees as $fee)
        {
            // school code
            if(isset($fee->school))
            {
                $school = $schoolRepository->findOneBy(['code' => $fee->school]);

                if(!$school)
                {
                    return new JsonResponse(['hydra:title' => 'School code not found in line '. $fee->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                return new JsonResponse(['hydra:title' => 'School code empty in line '. $fee->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            if(!isset($fee->code))
            {
                /*$feeCode = $feeRepository->findOneBy(['code' => $fee->code, 'school' => $school]);
                if($feeCode)
                {
                    return new JsonResponse(['hydra:title' => 'This code: '.$feeCode->getCode(). ' in line '. $fee->line .' already exist in current school'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }*/
                return new JsonResponse(['hydra:title' => 'Fee code empty in line '. $fee->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }
            /*else{
                return new JsonResponse(['hydra:title' => 'Fee code empty in line '. $fee->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }*/

            if(!isset($fee->name))
            {
                /*$feeName = $feeRepository->findOneBy(['name' => $fee->name, 'school' => $school]);
                if($feeName)
                {
                    return new JsonResponse(['hydra:title' => 'This name: '.$feeCode->getCode(). ' in line '. $fee->line .' already exist in current school'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }*/
                return new JsonResponse(['hydra:title' => 'Fee name empty in line '. $fee->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }
            /*else{
                return new JsonResponse(['hydra:title' => 'Fee name empty in line '. $fee->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }*/

            // fee type code
            if(isset($fee->feeType))
            {
                $feeType = $costAreaRepository->findOneBy(['code' => $fee->feeType]);
                if (!$feeType){
                    return new JsonResponse(['hydra:title' => 'Fee Type not found for code: '.$fee->feeType. ' in line '. $fee->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                return new JsonResponse(['hydra:title' => 'Fee Type empty in line '. $fee->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            // amount
            if(isset($fee->amount))
            {
                $amount = intval($fee->amount);
            }
            else{
                return new JsonResponse(['hydra:title' => 'Amount empty in line '. $fee->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            if(isset($fee->class))
            {
                $schoolClass = $schoolClassRepository->findOneBy(['code' => $fee->class]);
                if (!$schoolClass){
                    return new JsonResponse(['hydra:title' => 'Class not found for code: '.$fee->class. ' in line '. $fee->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $schoolClass = null;
            }

            if(isset($fee->feePaymentScheme))
            {
                $pensionScheme = $pensionSchemeRepository->findOneBy(['name' => $fee->feePaymentScheme]);
                if (!$pensionScheme){
                    return new JsonResponse(['hydra:title' => 'Fee Payment Scheme not found for name: '.$fee->feePaymentScheme. ' in line '. $fee->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $pensionScheme = null;
            }

            if(isset($fee->cycle))
            {
                $cycle = $cycleRepository->findOneBy(['code' => $fee->cycle]);
                if (!$cycle){
                    return new JsonResponse(['hydra:title' => 'Cycle not found for code: '.$fee->cycle. ' in line '. $fee->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $cycle = null;
            }

            // speciality code
            if(isset($fee->speciality))
            {
                $speciality = $specialityRepository->findOneBy(['code' => $fee->speciality]);
                if (!$speciality){
                    return new JsonResponse(['hydra:title' => 'Speciality not found for code: '.$fee->speciality. ' in line '. $fee->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $speciality = null;
            }

            // level name
            if(isset($fee->level))
            {
                $level = $levelRepository->findOneBy(['name' => $fee->level]);
                if (!$level){
                    return new JsonResponse(['hydra:title' => 'Level not found for name: '.$fee->level. ' in line '. $fee->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $level = null;
            }

            // training type code
            if(isset($fee->trainingType))
            {
                $trainingType = $trainingTypeRepository->findOneBy(['code' => $fee->trainingType]);
                if (!$trainingType){
                    return new JsonResponse(['hydra:title' => 'Training Type not found for code: '.$fee->trainingType. ' in line '. $fee->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $trainingType = null;
            }

            // budget line code
            if(isset($fee->budgetLine))
            {
                $budgetLine = $budgetLineRepository->findOneBy(['code' => $fee->budgetLine]);
                if (!$budgetLine){
                    return new JsonResponse(['hydra:title' => 'Budget Line not found for code: '.$fee->budgetLine. ' in line '. $fee->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $budgetLine = null;
            }

            // limit payment date

            $newFee = new Fee();

            $newFee->setInstitution($this->getUser()->getInstitution());
            $newFee->setYear($this->getUser()->getCurrentYear());
            $newFee->setSchool($school);

            $newFee->setCode($fee->code);
            $newFee->setName($fee->name);

            $newFee->setCostArea($feeType);
            $newFee->setClass($schoolClass);
            $newFee->setPensionScheme($pensionScheme);

            $newFee->setCycle($cycle);
            $newFee->setSpeciality($speciality);
            $newFee->setLevel($level);

            $newFee->setTrainingType($trainingType);
            $newFee->setBudgetLine($budgetLine);

            $newFee->setAmount($amount);
            $newFee->setPaymentDate(new \DateTimeImmutable());

            $newFee->setUser($this->getUser());

            $this->entityManager->persist($newFee);

            /*$item = new Item();
            $item->setFee($newFee);
            $item->setName($newFee->getName());
            $item->setReference($newFee->getCode());
            $item->setPrice($newFee->getAmount());
            $item->setCost($newFee->getAmount());
            $this->entityManager->persist($item);*/

        }

        $this->entityManager->flush();

        return new Response(null, Response::HTTP_OK);

        //return $this->json(['hydra:member' => $this->roleResultName($profile->getName())]);
    }

}



