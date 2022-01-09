<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\RestaurantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RestaurantController extends AbstractController
{
    #[Route('/restaurant/{id}', name: 'restaurant', methods: ['GET'])]
    public function index(RestaurantRepository $rr, ProductRepository $pr, $id = 0): Response
    {
        $restaurant = $rr->find($id);
        if($restaurant === null) return $this->redirectToRoute('home', [], 200);
        $products = $pr->findBy(array('restaurant' => $id));
        return $this->render('restaurant/restaurant.html.twig', [
            'restaurant' => $restaurant,
            'products' => $products
        ]);
    }
}
