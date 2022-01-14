<?php

namespace App\Controller;

use App\Entity\Command;
use App\Entity\CommandLine;
use App\Entity\Product;
use App\Entity\Restaurant;
use App\Form\ProductType;
use App\Form\RestaurantType;
use App\Repository\ProductRepository;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Faker\Factory;

#[Route('/restaurant')]
class RestaurantController extends AbstractController
{
    #[Route('s', name: 'restaurants', methods: ['GET'])]
    public function restaurants(RestaurantRepository $rr): Response
    {
        if(!$this->isGranted('ROLE_ADMIN')) return new Response('', 404);
        $restaurant = new Restaurant();
        $addRestaurantForm = $this->createForm(RestaurantType::class, $restaurant);

        $restaurants = $rr->findByUser($this->getUser());
        return $this->render('restaurant/restaurants.html.twig', [
            'restaurants' => $restaurants,
            'addRestaurantForm' => $addRestaurantForm->createView()
        ]);
    }

    #[Route('/{id}', name: 'restaurant', methods: ['GET'])]
    public function restaurant(RestaurantRepository $rr, ProductRepository $pr, $id = 0): Response
    {
        $restaurant = $rr->find($id);
        if($restaurant === null) return $this->redirectToRoute('home', [], 302);
        $responsable = false;
        foreach ($restaurant->getResponsable() as $key => $value) {
            if($value->getId() == $this->getUser()->getId()){
                $responsable = true;
                break;
            }
        }

        $products = $pr->findBy(array('restaurant' => $id));

        $product = new Product();
        $addProductForm = $this->createForm(ProductType::class, $product);
        return $this->render('restaurant/restaurant.html.twig', [
            'restaurant' => $restaurant,
            'products' => $products,
            'responsable' => $responsable,
            'addProductForm' => $addProductForm->createView()
        ]);
    }

    #[Route('/checkout', name: 'checkout', methods: ['POST'])]
    public function checkout(RestaurantRepository $rr, ProductRepository $pr, Request $request, EntityManagerInterface $entityManager): Response
    {
        $isAjax = $request->isXMLHttpRequest();
        if (!$isAjax) return new Response('', 404);

        $id = $request->get('id');
        $data = $request->get('data');
        $restaurant = $rr->find($id);
        if($restaurant === null) return $this->redirectToRoute('home', [], 302);

        $faker = Factory::create();
        $command = new Command();
        $command->setUser($this->getUser())
                ->setRestaurant($restaurant)
                ->setNumero($faker->numerify('#########'))
        ;

        $products = json_decode($data, true);

        $totalPrice = 0;
        foreach ($products as $key => $product) {
            $productOBJ = $pr->find($product['id']);
            if($productOBJ === null) continue;

            $commandLine = new CommandLine();
            $commandLine->setProduct($productOBJ)
                        ->setQuantity($product['quantity'])
                        ->setCommand($command)
            ;
            $entityManager->persist($commandLine);
            $totalPrice += $product['price'] * $product['quantity'];
        }

        $command->setTotalPrice($totalPrice);
        $entityManager->persist($command);
        $entityManager->flush();

        return $this->json(array(
            "code" => 200
        ),200);
    }

    #[Route('/new', name: 'add_restaurant', methods: ['POST'])]
    public function addRestaurant(Request $request, EntityManagerInterface $entityManager): Response
    {
        $isAjax = $request->isXMLHttpRequest();
        if (!$isAjax) return new Response('', 404);

        $restaurant = new Restaurant();
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $restaurant->setName(str_replace(array("'", '"'), "", $restaurant->getName()));
            $restaurant->setAddress(str_replace(array("'", '"'), "", $restaurant->getAddress()));
            $restaurant->setPhone(str_replace(array("'", '"'), "", $restaurant->getPhone()));
            $restaurant->addResponsable($this->getUser());
            $entityManager->persist($restaurant);
            $entityManager->flush();
            return $this->json(array(
                "code" => 200,
                "message" => "Restaurant successfully added. The page will reload in 3 seconds"
            ),200);
        }

        return $this->json(array(
            "code" => 200,
            "errors" => $form->getErrors()
        ),200);
    }

    #[Route('/edit', name: 'edit_restaurant', methods: ['PUT'])]
    public function editRestaurant(Request $request, EntityManagerInterface $entityManager, RestaurantRepository $rr): Response
    {
        $isAjax = $request->isXMLHttpRequest();
        if (!$isAjax) return new Response('', 404);

        $restaurant = $rr->find($request->get('id'));
        if($restaurant === null) return $this->json(array("code" => 400, "message" => "Bad request, restaurant does not exist or id is missing"),400);

        // Allows you to verify that you are responsible for the restaurant you want to modify
        foreach ($restaurant->getResponsable() as $key => $value) {
            if($value->getId() == $this->getUser()->getId()){
                $continue = true;
                break;
            }
        }
        if(!isset($continue)) return $this->json(array("code" => 403, "message" => "You do not have permission to edit this restaurant"),403);

        $form = $this->createForm(RestaurantType::class, $restaurant, array('method' => 'PUT'));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $restaurant->setName(str_replace(array("'", '"'), "", $restaurant->getName()));
            $restaurant->setAddress(str_replace(array("'", '"'), "", $restaurant->getAddress()));
            $restaurant->setPhone(str_replace(array("'", '"'), "", $restaurant->getPhone()));
            $restaurant->setUpdatedAt(new \DateTime());
            $entityManager->flush();
            return $this->json(array(
                "code" => 200,
                "message" => "Restaurant successfully modified",
                "info" => array(
                    'name' => $restaurant->getName(),
                    'address' => $restaurant->getAddress(),
                    'phone' => $restaurant->getPhone(),
                    'updatedAt' => $restaurant->getUpdatedAt()->format("m/d/Y, H:i:s")
                )
            ),200);
        }

        return $this->json(array(
            "code" => 200,
            "errors" => $form->getErrors()
        ),200);
    }

    #[Route('/delete', name: 'delete_restaurant', methods: ['DELETE'])]
    public function deleteRestaurant(Request $request, EntityManagerInterface $entityManager, RestaurantRepository $rr): Response
    {
        $isAjax = $request->isXMLHttpRequest();
        if (!$isAjax) return new Response('', 404);

        $restaurant = $rr->find($request->get('id'));
        if($restaurant === null) return $this->json(array("code" => 400, "message" => "Bad request, restaurant does not exist or id is missing"),400);

        // Allows you to verify that you are responsible for the restaurant you want to delete
        foreach ($restaurant->getResponsable() as $key => $value) {
            if($value->getId() == $this->getUser()->getId()){
                $continue = true;
                break;
            }
        }
        if(!isset($continue)) return $this->json(array("code" => 403, "message" => "You do not have permission to delete this restaurant"),403);

        $entityManager->remove($restaurant);
        $entityManager->flush();
        return $this->json(array(
            "code" => 200,
            "message" => "Restaurant successfully deleted"
        ),200);
    }
}
