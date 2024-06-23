<?php

namespace App\Controller;

use App\Document\Ingredient;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;


use Knp\Component\Pager\PaginatorInterface;


class IngredientController extends AbstractController
{
    private DocumentManager $documentManager;
    private SerializerInterface $serializer;

    public function __construct(DocumentManager $documentManager, SerializerInterface $serializer)
    {
        $this->documentManager = $documentManager;
        $this->serializer = $serializer;
    }






    #[Route('/all-ingredients', name: 'get_all_ingredients_no_pagination', methods: ['GET'])]
    public function getAllIngredientsWithoutPagination(): JsonResponse
    {
        $ingredients = $this->documentManager->getRepository(Ingredient::class)->findAll();

        $serializedIngredients = $this->serializer->serialize($ingredients, 'json');

        return new JsonResponse(json_decode($serializedIngredients, true), Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }




    /* ______________ Get All With Pagination ______________ */

    #[Route('/ingredients', name: 'get_all_ingredients', methods: ['GET'])]
    public function listIngredients(Request $request, PaginatorInterface $paginator): JsonResponse
    {
        $ingredientsQuery = $this->documentManager->getRepository(Ingredient::class)->createQueryBuilder();

        $pagination = $paginator->paginate(
            $ingredientsQuery,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 2)
        );

        $serializedIngredients = $this->serializer->serialize($pagination->getItems(), 'json');

        $response = [
            'items' => json_decode($serializedIngredients, true),
            'pagination' => [
                'currentPage' => $pagination->getCurrentPageNumber(),
                'itemsPerPage' => $pagination->getItemNumberPerPage(),
                'totalItems' => $pagination->getTotalItemCount(),
                'totalPages' => ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage()),
            ],
        ];

        return new JsonResponse($response, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }


    /* ______________ Get By Id  ______________ */

    #[Route('/ingredients/{id}', name: 'get_ingredient_by_id', methods: ['GET'])]
    public function detailIngredient(string $id): Response
    {

        $ingredient = $this->documentManager->getRepository(Ingredient::class)->find($id);


        if (!$ingredient) {
            return $this->json(['error' => 'Ingredient not found'], Response::HTTP_NOT_FOUND);
        }

        $serializedIngredient = $this->serializer->serialize($ingredient, 'json');

        return new Response($serializedIngredient, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }



    /* ______________ Insert  ______________ */

    #[Route('/ingredients', name: 'insert_ingredient', methods: ['POST'])]
    public function createIngredient(Request $request): Response
    {

        $requestData = json_decode($request->getContent(), true);


        $ingredient = new Ingredient();
        $ingredient->setName($requestData['name'] ?? null);
        $ingredient->setType($requestData['type'] ?? null);

        $this->documentManager->persist($ingredient);
        $this->documentManager->flush();


        return $this->json(['id' => $ingredient->getId()]);
    }




    /* ______________ Update  ______________ */

    #[Route('/ingredients/{id}', name: 'update_ingredient', methods: ['PUT'])]
    public function updateIngredient(string $id, Request $request): Response
    {

        $ingredient = $this->documentManager->getRepository(Ingredient::class)->find($id);


        if (!$ingredient) {
            return $this->json(['error' => 'Ingredient not found'], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), true);


        $ingredient->setName($requestData['name'] ?? $ingredient->getName());
        $ingredient->setType($requestData['type'] ?? $ingredient->getType());

        $this->documentManager->flush();

        return $this->json(['id' => $ingredient->getId()]);
    }





    /* ______________ Delete By Id ______________ */

    #[Route('/ingredients/{id}', name: 'delete_ingredient', methods: ['DELETE'])]
    public function deleteIngredient(int $id): Response
    {

        $ingredient = $this->documentManager->getRepository(Ingredient::class)->find($id);

        if (!$ingredient) {
            return $this->json(['error' => 'Ingredient not found'], Response::HTTP_NOT_FOUND);
        }
        $this->documentManager->remove($ingredient);
        $this->documentManager->flush();
        return $this->json(['message' => 'Ingredient deleted successfully']);
    }




    /* ______________ Delete All  ______________ */

    #[Route('/ingredients', name: 'api_delete_all_ingredients', methods: ['DELETE'])]
    public function deleteAllIngredients(): Response
    {
        $ingredients = $this->documentManager->getRepository(Ingredient::class)->findAll();
        foreach ($ingredients as $ingredient) {
            $this->documentManager->remove($ingredient);
        }

        $this->documentManager->flush();

        return $this->json(['message' => 'All ingredients deleted successfully']);
    }
}
