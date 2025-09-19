<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class SecuredController extends AbstractController
{
    protected function requireLogin(SessionInterface $session): ?RedirectResponse
    {
        if (!$session->get('logged_in')) {
            return $this->redirectToRoute('app_login');
        }
        return null;
    }
}
