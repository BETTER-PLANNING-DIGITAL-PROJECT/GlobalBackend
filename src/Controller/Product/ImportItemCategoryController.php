<?php

namespace App\Controller\Product;

use App\Entity\Product\ItemCategory;
use App\Repository\Product\ItemCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class ImportItemCategoryController extends AbstractController
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

    public function __invoke(Request $request, ItemCategoryRepository $itemCategoryRepository): Response
    {
        $data = $this->jsondecode();

        if(!isset($data->data))
            return throw new BadRequestHttpException('file is compulsory');

        $itemCategories = $data->data;

        if (!$itemCategories) {
            throw new BadRequestHttpException('"file" is required');
        }

        foreach ($itemCategories as $itemCategory)
        {

            if(!isset($itemCategory->name))
            {
                return new JsonResponse(['hydra:title' => 'Name empty in line '. $itemCategory->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }
            else{
                $category = $itemCategoryRepository->findOneBy(['name' => $itemCategory->name, 'institution' => $this->getUser()->getInstitution()]);
                if($category)
                {
                    return new JsonResponse(['hydra:title' => 'Item category: '.$category->getName(). ' in line '. $itemCategory->line .' already exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }

            $newItemCategory = new ItemCategory();
            $newItemCategory->setName($itemCategory->name);

            $newItemCategory->setInstitution($this->getUser()->getInstitution());
            $newItemCategory->setUser($this->getUser());
            $newItemCategory->setYear($this->getUser()->getCurrentYear());

            $this->entityManager->persist($newItemCategory);
        }

        $this->entityManager->flush();

        return new Response(null, Response::HTTP_OK);
    }

}