<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class ArchiveMultipleController extends AbstractController
{
    private Request $req;
    public function __construct(Request $req, EntityManagerInterface $manager)
    {
        $this->req = $req;
    }

    public function archiveMultiple(): Response
    {
        $ids = json_decode($this->req->getContent(), true);

        if (!is_array($ids)) {
            return new Response('Invalid request body.', Response::HTTP_BAD_REQUEST);
        }

        foreach ($ids as $id){

            $this->repo->archiveItems($id);
        }

        return new Response("Successfully archived", Response::HTTP_OK);
    }

    public function __invoke():void
    {
        $this->archiveMultiple();
    }



}
