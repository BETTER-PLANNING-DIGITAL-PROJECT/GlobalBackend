<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Repository\School\Schooling\Configuration\FeeRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\Security\Institution\InstitutionRepository;
use App\Repository\Security\Session\YearRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OpenOrCloseFeePaymentController extends AbstractController
{
    private Request $req;
    private EntityManagerInterface $entityManager;
    private SchoolClassRepository $schoolClassRepository;
    private YearRepository $yearRepository;
    private InstitutionRepository $institutionRepository;
	private FeeRepository $feeRepository;

    /**
     * @param Request $req
     * @param EntityManagerInterface $entityManager
     * @param FeeRepository $feeRepository
	 * @param SchoolClassRepository $schoolClassRepository
     * @param YearRepository $yearRepository
     * @param InstitutionRepository $institutionRepository
     */
    public function __construct(Request                                $req,
                                EntityManagerInterface                 $entityManager,
                                FeeRepository                          $feeRepository,
                                YearRepository                         $yearRepository,
								SchoolClassRepository                  $schoolClassRepository,
                                InstitutionRepository                  $institutionRepository
                                )
    {
        $this->req = $req;
        $this->entityManager = $entityManager;
		$this->schoolClassRepository = $schoolClassRepository;
        $this->feeRepository = $feeRepository;
        $this->yearRepository = $yearRepository;
        $this->institutionRepository = $institutionRepository;
    }

    // Mark

    // Open Or Close Fees Payment
    #[Route('api/open/close/fee/payment/per/class/{schoolClasses}', name: 'open_or_close_fee_payment_per_class')]
    public function openOrCloseFeePaymentPerClass(mixed $schoolClasses): JsonResponse
    {
        $schoolClassData = json_decode($schoolClasses, true);
        extract($schoolClassData);

        foreach ($classIds as $classId) {
            $class = $this->schoolClassRepository->find($classId);
            $isPaymentFee = !$class->isIsPaymentFee();
            $class->setIsPaymentFee($isPaymentFee);

            // Retrieve the ClassProgram entities related to the current SchoolClass
            $fees = $this->feeRepository->findBy(['class' => $class]);

            // Toggle the isPaymentStudentFee property for each ClassProgram
            foreach ($fees as $fee) {
                $fee->setIsPaymentFee($isPaymentFee);
            }
        }

        $this->entityManager->flush();
        return $this->json([]);
    }

    #[Route('/api/open/close/fee/payment/per/fee/{fees}', name: 'open_or_close_fee_payment_per_fee')]
    public function openOrCloseFeePaymentPerFee(mixed $fees): JsonResponse
    {
        $feeData = json_decode($fees, true);
        extract($feeData);

        foreach ($feeIds as $feeId) {

            // Retrieve the fee entities related to the fee id
            $fee = $this->feeRepository->find($feeId);

            // Toggle the isIsPaymentFee property for the fee
            $fee->setIsPaymentFee(!$fee->isIsPaymentFee());
        }

        $this->entityManager->flush();
        return $this->json([]);
    }


}
