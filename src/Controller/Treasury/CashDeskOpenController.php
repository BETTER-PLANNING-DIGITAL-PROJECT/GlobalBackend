<?php
namespace App\Controller\Treasury;

use App\Entity\Security\User;
use App\Entity\Treasury\CashDesk;
use App\Repository\Treasury\CashDeskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CashDeskOpenController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly CashDeskRepository $cashDeskRepository,
                                private readonly EntityManagerInterface $manager)
    {
    }

    public function __invoke(mixed $data, Request $request): JsonResponse|CashDesk
    {
        // TODO: Implement process() method.
        if (!$data instanceof CashDesk){
            return new JsonResponse(['hydra:title' => 'Invalid entity process'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        // Get user cash desk
        $vault = $this->cashDeskRepository->findOneBy(['operator' => $this->getUser()]);
        if (!$vault)
        {
            return new JsonResponse(['hydra:title' => 'You are not a cashier!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        // Cash Desk
        if ($data !== $vault)
        {
            // Check if cash desk is a vault
            if(!$vault->isIsMain())
            {
                return new JsonResponse(['hydra:title' => 'Sorry you are not a main cashier'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }

            /*// check if main cash desk exist
            $vault = $this->cashDeskRepository->findOneBy(['isMain' => true, 'institution' => $this->getUser()->getInstitution()]);
            if (!$vault)
            {
                return new JsonResponse(['hydra:title' => 'No main cashier found'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }

            if ($vault->getOperator() !== $this->getUser())
            {
                return new JsonResponse(['hydra:title' => 'Sorry you are not the main cashier'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }*/
        }

        $requestData = json_decode($request->getContent(), true);

        $amount = $requestData['beginningBalance'];
        if (!is_numeric($amount))
        {
            return new JsonResponse(['hydra:title' => 'Amount should be a number'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }
        elseif ($amount < 0)
        {
            return new JsonResponse(['hydra:title' => 'Amount should not be less than zero '], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        // Check if beginning balance is equal to cash desk balance

        // Update cash desk
        $data->setLastOpenAt(new \DateTimeImmutable());
        $data->setBeginningBalance($amount);
        $data->setIsOpen(true);
        $data->setUpdatedAt(new \DateTimeImmutable());

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
