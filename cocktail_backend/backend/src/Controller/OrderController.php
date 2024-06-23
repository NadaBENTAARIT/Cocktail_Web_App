<?php

namespace App\Controller;

use App\Document\Cocktail;
use App\Document\Order;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class OrderController extends AbstractController
{
    private DocumentManager $documentManager;
    private SerializerInterface $serializer;

    public function __construct(DocumentManager $documentManager, SerializerInterface $serializer)
    {
        $this->documentManager = $documentManager;
        $this->serializer = $serializer;
    }



    #[Route('/orders', name: 'get_all_orders', methods: ['GET'])]
    public function listOrders(): Response
    {
        $orderRepository = $this->documentManager->getRepository(Order::class);
    
        $orders = $orderRepository->createQueryBuilder()
            ->sort('createdAt', 'desc')
            ->getQuery()
            ->execute();
    
        $serializedOrders = $this->serializer->serialize($orders, 'json');
    
        return new JsonResponse(json_decode($serializedOrders, true), Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /* ______________Get By id  ______________ */

    #[Route('/orders/{id}', name: 'get_order_by_id', methods: ['GET'])]
    public function detailOrder(string $id): Response
    {
        $order = $this->documentManager->getRepository(Order::class)->find($id);
        if (!$order) {
            return $this->json(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }
        $serializedOrder = $this->serializer->serialize($order, 'json');
        return new Response($serializedOrder, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }





    /* ______________ Insert  ______________ */

    #[Route('/orders', name: 'create_order', methods: ['POST'])]
    public function createOrder(Request $request): Response
    {
        $requestData = json_decode($request->getContent(), true);
        $order = new Order();

        $cocktailId = $requestData['cocktail_id'] ?? null;
        $cocktail = $this->documentManager->getRepository(Cocktail::class)->find($cocktailId);
        if (!$cocktail) {
            return $this->json(['error' => 'Cocktail not found'], Response::HTTP_NOT_FOUND);
        }

        $order->setCocktail($cocktail);

        $this->documentManager->persist($order);
        $this->documentManager->flush();

        return $this->json(['id' => $order->getId()]);
    }






    /* ______________ Update ______________ */

    #[Route('/orders/{id}', name: 'update_order', methods: ['PUT'])]
    public function updateOrder(string $id, Request $request): Response
    {
        $order = $this->documentManager->getRepository(Order::class)->find($id);
        if (!$order) {
            return $this->json(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), true);

        $newStatus = $requestData['status'] ?? $order->getStatus();

        $order->setStatus($newStatus);

        $newCocktailId = $requestData['cocktail_id'] ?? null;
        if ($newCocktailId !== null && $newCocktailId !== $order->getCocktail()->getId()) {
            $newCocktail = $this->documentManager->getRepository(Cocktail::class)->find($newCocktailId);
            if (!$newCocktail) {
                return $this->json(['error' => 'New Cocktail not found'], Response::HTTP_NOT_FOUND);
            }
            $order->setCocktail($newCocktail);
        }

        $this->documentManager->flush();

        return $this->json(['id' => $order->getId()]);
    }






    /* ______________ Delete  ______________ */

    #[Route('/orders/{id}', name: 'delete_order', methods: ['DELETE'])]
    public function deleteOrder(string $id): Response
    {
        $order = $this->documentManager->getRepository(Order::class)->find($id);
        if (!$order) {
            return $this->json(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $this->documentManager->remove($order);
        $this->documentManager->flush();

        return $this->json(['message' => 'Order deleted successfully']);
    }
}
