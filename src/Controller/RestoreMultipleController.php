<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class RestoreMultipleController extends AbstractController
{
    private Request $req;
    public function __construct(Request $req, EntityManagerInterface $manager)
    {
        $this->req = $req;
        $this->repo = $njangiRepository;
    }

    public function restoreMultiple(): Response
    {
        $ids = json_decode($this->req->getContent(), true);

        if (!is_array($ids)) {
            return new Response('Invalid request body.', Response::HTTP_BAD_REQUEST);
        }

        foreach ($ids as $id){

            $this->repo->restoreItems($id);
        }

        return new Response("Successfully restored", Response::HTTP_OK);
    }

    public function __invoke():void
    {
        $this->restoreMultiple();
    }



}
