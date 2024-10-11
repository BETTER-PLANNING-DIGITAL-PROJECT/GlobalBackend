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

class CreateCashDeskController extends AbstractController
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

        if(!$this->getUser())
        {
            return new JsonResponse(['hydra:title' => 'User not found'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if($this->cashDeskRepository->findOneBy(['operator' => $this->getUser()]))
        {
            return new JsonResponse(['hydra:title' => 'User already a cashier'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        $requestData = json_decode($request->getContent(), true);

        // Check  Main
        $isMain = $requestData['isMain'];
        if($isMain == true)
        {
            // check if main cash desk exist
            $vault = $this->cashDeskRepository->findOneBy(['isMain' => true, 'institution' => $this->getUser()->getInstitution()]);
            if($vault)
            {
                return new JsonResponse(['hydra:title' => 'Main cashier already exist'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }

        }

        // Check code
        $code = $requestData['code'];
        $cashDeskCode = $this->cashDeskRepository->findOneBy(['code' => $code, 'institution' => $this->getUser()->getInstitution()]);
        if ($cashDeskCode)
        {
            return new JsonResponse(['hydra:title' => 'Code already exist'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

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
