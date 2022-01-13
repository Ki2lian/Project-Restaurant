<?php

namespace App\Controller;

use App\Repository\CommandRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/order')]
class OrderController extends AbstractController
{
    #[Route('s', name: 'orders')]
    public function commands(CommandRepository $cr): Response
    {
        $orders = $cr->findBy(array('user' => $this->getUser()), array('id' => 'DESC'));
        return $this->render('order/orders.html.twig', [
            'orders' => $orders,
        ]);
    }
}
