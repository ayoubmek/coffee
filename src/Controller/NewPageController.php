<?php
// src/Controller/NewPageController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewPageController extends AbstractController
{
    #[Route('/new-page', name: 'app_new_page')]
    public function index(): Response
    {
        return new Response('New page works');
    }
}