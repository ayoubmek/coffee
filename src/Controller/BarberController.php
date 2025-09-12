<?php

// src/Controller/BarberController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BarberController extends AbstractController
{
    #[Route('/barber', name: 'app_barber')]
    public function index(): Response
    {
        return new Response('<h1>Barber Page Works!</h1>');
    }
}
