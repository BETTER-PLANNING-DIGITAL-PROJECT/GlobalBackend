<?php
namespace App\Controller\Treasury;

use App\Entity\Security\User;
use App\Entity\Treasury\CashDeskHistory;
use App\Entity\Treasury\CashDeskOperation;
use App\Repository\Treasury\CashDeskHistoryRepository;
use App\Repository\Treasury\CashDeskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ValidateCashDeskOperationController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly EntityManagerInterface $em)
    {
    }
    public function __invoke(CashDeskOperation $cashDeskOperation, CashDeskRepository $cashDeskRepository, CashDeskHistoryRepository $cashDeskHistoryRepository): JsonResponse
    {

        try {
            $em = $this->getUser()->getUserIdentifier();

            // Vault of branch
            $vault = $cashDeskRepository->findOneBy(['isMain' => true, 'branch' => $this->getUser()->getBranch(), 'institution' => $this->getUser()->getInstitution()]);
            if (!$vault)
            {
                return new JsonResponse(['hydra:title' => 'Main cash desk not found!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }

            // Cash Desk
            if ($cashDeskOperation->getCashDesk() !== $cashDeskRepository->findOneBy(['operator' => $this->getUser(), 'branch' => $this->getUser()->getBranch(), 'institution' => $this->getUser()->getInstitution()]))
            {
                return new JsonResponse(['hydra:title' => 'sorry you can not validate this operation!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }

            if (!$cashDeskOperation->getCashDesk()->isIsOpen())
            {
                return new JsonResponse(['hydra:title' => 'your cash desk is not open!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }

            // Cashier Adjustment Start
            $cashDesk = $cashDeskOperation->getCashDesk();
            $operationCategory = $cashDeskOperation->getOperationCategory();
            $amount = $cashDeskOperation->getAmount();

            if ($operationCategory->getCode() == 'WITHDRAWAL')
            {
                // Write cash desk history
                $cashDeskHistory = new CashDeskHistory();

                $cashDeskHistory->setCashDesk($vault);
                $cashDeskHistory->setReference($cashDeskOperation->getReference());
                $cashDeskHistory->setDescription($cashDeskOperation->getDescription());
                $cashDeskHistory->setDebit($amount);
                $cashDeskHistory->setCredit(0);
                // balance : en bas
                $cashDeskHistory->setDateAt(new \DateTimeImmutable());

                $cashDeskHistory->setBranch($this->getUser()->getBranch());
                $cashDeskHistory->setInstitution($this->getUser()->getInstitution());
                $cashDeskHistory->setUser($this->getUser());
                $this->em->persist($cashDeskHistory);

                // Update vault daily deposit balance
                $vault->setDailyDeposit($vault->getDailyDeposit() + $amount);

                // Update vault balance
                $cashDeskHistories = $cashDeskHistoryRepository->findBy(['cashDesk' => $vault]);

                $debit = $amount; $credit = 0;

                foreach ($cashDeskHistories as $item)
                {
                    $debit += $item->getDebit();
                    $credit += $item->getCredit();
                }

                $balance = $debit - $credit;

                $cashDeskHistory->setBalance($balance);
                $vault->setBalance($balance);
            }

            if ($operationCategory->getCode() == 'DEPOSIT')
            {
                // Write cash desk history
                $cashDeskHistory = new CashDeskHistory();

                $cashDeskHistory->setCashDesk($cashDesk);
                $cashDeskHistory->setReference($cashDeskOperation->getReference());
                $cashDeskHistory->setDescription($cashDeskOperation->getDescription());
                $cashDeskHistory->setDebit($amount);
                $cashDeskHistory->setCredit(0);
                // balance : en bas
                $cashDeskHistory->setDateAt(new \DateTimeImmutable());

                $cashDeskHistory->setBranch($this->getUser()->getBranch());
                $cashDeskHistory->setInstitution($this->getUser()->getInstitution());
                $cashDeskHistory->setUser($this->getUser());
                $this->em->persist($cashDeskHistory);

                // Update cash desk daily deposit balance
                $cashDesk->setDailyDeposit($cashDesk->getDailyDeposit() + $amount);

                // Update cash desk balance
                $cashDeskHistories = $cashDeskHistoryRepository->findBy(['cashDesk' => $cashDesk]);

                $debit = $amount; $credit = 0;

                foreach ($cashDeskHistories as $item)
                {
                    $debit += $item->getDebit();
                    $credit += $item->getCredit();
                }

                $balance = $debit - $credit;

                $cashDeskHistory->setBalance($balance);
                $cashDesk->setBalance($balance);
            }

            $cashDeskOperation->setIsValidate(true);
            $cashDeskOperation->setValidateBy($this->getUser());
            $cashDeskOperation->setValidateAt(new \DateTimeImmutable());

            $this->em->flush();

            return new JsonResponse(['hydra:title' => 'cash_desk_operation.validate.successful'], Response::HTTP_OK, ['Content-Type', 'application/json']);

        } catch (\Exception $e) {

            return new JsonResponse(['hydra:title' => $e->getMessage()], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            //return new JsonResponse(['hydra:title' => 'cash_desk_operation.validate.failed'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

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
