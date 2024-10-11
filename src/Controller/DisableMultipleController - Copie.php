<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class DisableMultipleController extends AbstractController
{
    private Request $req;
    public function __construct(Request $req)
    {
        $this->req = $req;
        $this->repo = $njangiRepository;
    }

    public function disableMultiple(): Response
    {
        $ids = json_decode($this->req->getContent(), true);

        if (!is_array($ids)) {
            return new Response('Invalid request body.', Response::HTTP_BAD_REQUEST);
        }

        foreach ($ids as $id){
            $this->repo->disableItems($id);
        }


        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    public function __invoke():void
    {
        $this->disableMultiple();

    }



}
