<?php
namespace App\Controller;
use App\Entity\Payment; 
use App\Entity\Produit; 

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;

use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Repository\HistoryRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ServiceController extends AbstractController
{

      #[Route('/service', name: 'app_service')]
    public function index1(CategorieRepository $categorieRepository,ProduitRepository $produitRepository): Response
    { 
        $categories = $categorieRepository->findAll();
        $produits = $produitRepository->findAll();

        return $this->render('service/service.html.twig', [
            'categories' => $categories,
            'produits' => $produits,

        ]);
    }

    #[Route('/produit/{id}', name: 'app_produit_details')]
public function details(Produit $produit): Response
{
    return $this->render('produit/details.html.twig', [
        'produit' => $produit,
    ]);
}

    
    // #[Route('/service', name: 'app_service', methods: ['GET'])]
    // public function index1(
    //     Request $request,
    //     CategoryRepository $categoryRepository,
    //     UserRepository $userRepository
    // ): Response
    // {
    //     $id = (int) $request->query->get('id');
    //     $barber = $userRepository->find($id);

    //     return $this->render('service/service.html.twig', [
    //         'categories' => $categoryRepository->findAll(),
    //         'invoiceId' => (int) $request->query->get('id'),
    //         'barber'     => $barber,
    //         'barberId'   => $id,
    //     ]);
    // }

    
    #[Route('/service/pay', name: 'service_pay', methods: ['POST'])]
public function pay(
    Request                $request,
    EntityManagerInterface $em,
    UserRepository         $userRepository
): JsonResponse {
    if ($this->container->has('profiler')) {
        $this->container->get('profiler')->disable();
    }

    $data     = json_decode($request->getContent(), true);
    $amount   = $data['amount']   ?? null;
    $barberId = $data['barberId'] ?? null;

    if (!$amount || !is_numeric($amount) || !$barberId) {
        return new JsonResponse(['error' => 'Invalid data'], 400);
    }

    $barber = $userRepository->find($barberId);
    if (!$barber) {
        return new JsonResponse(['error' => 'Barber not found'], 404);
    }

    $payment = new Payment();
    $payment->setUser($barber);       
    $payment->setAmount((string) $amount);
    $payment->setPaymentType('cach');
    $em->persist($payment);
    $em->flush();

    return new JsonResponse(['ok' => true, 'paymentId' => $payment->getId()]);
}
    #[Route('/payments', name: 'app_payments')]
public function index(UserRepository $userRepository, HistoryRepository $historyRepository): Response
{
     $barbers   = $userRepository->findBy(['role' => 'barber']);
     $histories = $historyRepository->findBy([], ['date' => 'DESC']);

    return $this->render('payment/liste.html.twig', [
        'barbers'   => $barbers,
        'histories' => $histories,
    ]);
}
}