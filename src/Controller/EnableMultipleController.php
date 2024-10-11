<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class EnableMultipleController extends AbstractController
{
    private Request $req;
    private $manager;
    public function __construct(Request $req, EntityManagerInterface $manager)
    {
        $this->req = $req;
        $this->repo = $njangiRepository;
        $this->manager = $manager;
    }

    #[Route('/update/state', name: 'api_update_state', methods: ['PUT'])]
    public function updateMultiple(): Response
    {
        $ids = json_decode($this->req->getContent(), true);

        if (!is_array($ids)) {
            return new Response('Invalid request body.', Response::HTTP_BAD_REQUEST);
        }

        foreach ($ids as $id){

            $this->repo->enableItems($id);
//            foreach ($id as $item){
//                $this->repo->enableItems($item);
//                $all = $this->repo->findOneBy(['id' => $item]);
//                $all->setIsEnable(true);
//            }
//
//            $this->manager->flush();
        }

        return new Response("Successfully updated", Response::HTTP_OK);
    }

    public function __invoke():void
    {
        $this->updateMultiple();
    }



}
