<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/new', name: 'add_product', methods: ['POST'])]
    public function addProduct(Request $request, RestaurantRepository $rr, EntityManagerInterface $entityManager): Response
    {
        $isAjax = $request->isXMLHttpRequest();
        if (!$isAjax) return new Response('', 404);

        $idRestaurant = $request->get('idRestaurant');
        $restaurant = $rr->find($idRestaurant);
        if($restaurant === null) return $this->json(array("code" => 400, "message" => "Bad request, restaurant does not exist or id is missing"),400);

        // Allows you to verify that you are responsible for add product to the restaurant
        foreach ($restaurant->getResponsable() as $key => $value) {
            if($value->getId() == $this->getUser()->getId()){
                $continue = true;
                break;
            }
        }
        if(!isset($continue)) return $this->json(array("code" => 403, "message" => "You do not have permission to add product to this restaurant"),403);


        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setRestaurant($restaurant);
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->json(array(
                "code" => 200,
                "message" => "Product successfully added",
                "info" => array(
                    'id' => $product->getId(), // sans flush ça sera null !!!!!!!
                    "name" => $product->getName(),
                    "price" => $product->getPrice(),
                    "description" => $product->getDescription(),
                    "csrfToken" => $this->container->get('security.csrf.token_manager')->getToken('product')->getValue()
                )
            ),200);
        }

        return $this->json(array(
            "code" => 200,
            "errors" => $form->getErrors()
        ),200);
    }

    #[Route('/edit', name: 'edit_product', methods: ['PUT'])]
    public function editProduct(Request $request, EntityManagerInterface $entityManager, RestaurantRepository $rr, ProductRepository $pr): Response
    {
        $isAjax = $request->isXMLHttpRequest();
        if (!$isAjax) return new Response('', 404);

        $idRestaurant = $request->get('idRestaurant');
        $restaurant = $rr->find($idRestaurant);
        if($restaurant === null) return $this->json(array("code" => 400, "message" => "Bad request, restaurant does not exist or id is missing"),400);

       // Allows you to verify that you are responsible for edit product to the restaurant
       foreach ($restaurant->getResponsable() as $key => $value) {
            if($value->getId() == $this->getUser()->getId()){
                $continue = true;
                break;
            }
        }
        if(!isset($continue)) return $this->json(array("code" => 403, "message" => "You do not have permission to edit product to this restaurant"),403);

        $product = $pr->find($request->get('id'));
        if($product === null) return $this->json(array("code" => 400, "message" => "Bad request, product does not exist or id is missing"),400);

        $form = $this->createForm(ProductType::class, $product, array('method' => 'PUT'));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setUpdatedAt(new \DateTime());
            $entityManager->flush();
            return $this->json(array(
                "code" => 200,
                "message" => "Product successfully modified",
                "info" => array(
                    "name" => $product->getName(),
                    "price" => $product->getPrice(),
                    "description" => $product->getDescription(),
                )
            ),200);
        }

        return $this->json(array(
            "code" => 200,
            "errors" => $form->getErrors()
        ),200);
    }

    #[Route('/delete', name: 'delete_product', methods: ['DELETE'])]
    public function deleteProduct(Request $request, EntityManagerInterface $entityManager, RestaurantRepository $rr, ProductRepository $pr): Response
    {
        $isAjax = $request->isXMLHttpRequest();
        if (!$isAjax) return new Response('', 404);

        $idRestaurant = $request->get('idRestaurant');
        $restaurant = $rr->find($idRestaurant);
        if($restaurant === null) return $this->json(array("code" => 400, "message" => "Bad request, restaurant does not exist or id is missing"),400);

        // Allows you to verify that you are responsible for delete product to the restaurant
       foreach ($restaurant->getResponsable() as $key => $value) {
            if($value->getId() == $this->getUser()->getId()){
                $continue = true;
                break;
            }
        }
        if(!isset($continue)) return $this->json(array("code" => 403, "message" => "You do not have permission to delete product to this restaurant"),403);

        $product = $pr->find($request->get('id'));
        if($product === null) return $this->json(array("code" => 400, "message" => "Bad request, product does not exist or id is missing"),400);

        $entityManager->remove($product);
        $entityManager->flush();
        return $this->json(array(
            "code" => 200,
            "message" => "Product successfully deleted"
        ),200);
    }
}
