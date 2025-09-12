<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TeamController extends AbstractController
{
    #[Route('/team', name: 'app_team')]
    public function index(UserRepository $userRepository): Response
    {
        $barbers = $userRepository->findBy(['role' => 'barber']);

        return $this->render('team/team.html.twig', [
            'barbers' => $barbers,
        ]);
    }

    #[Route('/team/{id}', name: 'app_team_show', requirements: ['id' => '\d+'])]
    public function show(int $id, UserRepository $userRepository): Response
    {
        $barber = $userRepository->find($id);

        if (!$barber || $barber->getRole() !== 'barber') {
            throw $this->createNotFoundException('Barber not found');
        }

        return $this->render('team/show.html.twig', [
            'barber' => $barber,
        ]);
    }
}