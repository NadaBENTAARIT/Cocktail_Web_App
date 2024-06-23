<?php
/**
 * Fichier CocktailController.php
 * php version 8.3.2
 * @category Controller
 * @package  App\Controller
 * @author nbentaarit@ats-digital.com
 * @link http://127.0.0.1:8000/
 * @license https://opensource.org/licenses/MIT MIT License


 */

namespace App\Controller;

use Knp\Component\Pager\PaginatorInterface;

use App\Document\Cocktail;
use App\Document\Ingredient;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @category Controller
 * @package  App\Controller
 */
class CocktailController extends AbstractController
{
    private DocumentManager $_documentManager;
    private SerializerInterface $_serializer;

    /**
     * CocktailController constructor.
     *
     * @param DocumentManager $documentManager
     * @param SerializerInterface $serializer
     */
    public function __construct(DocumentManager $documentManager, SerializerInterface $serializer)
    {
        $this->_documentManager = $documentManager;
        $this->_serializer = $serializer;
    }

    /**
     * Get all cocktails.
     *
     * @return Response
     */
    #[Route('/cocktails', name: 'get_all_cocktails', methods: ['GET'])]
    public function listCocktails(): Response
    {
        $cocktails = $this->_documentManager->getRepository(Cocktail::class)->findAll();
        $serializedCocktails = $this->_serializer->serialize($cocktails, 'json');
        return new Response($serializedCocktails, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * Get cocktail by ID.
     *
     * @param string $id
     * @return Response
     */
    #[Route('/cocktails/{id}', name: 'get_cocktail_by_id', methods: ['GET'])]
    public function detailCocktail(string $id): Response
    {
        $cocktail = $this->_documentManager->getRepository(Cocktail::class)->find($id);
        if (!$cocktail) {
            return $this->json(['error' => 'Cocktail not found'], Response::HTTP_NOT_FOUND);
        }
        $serializedCocktail = $this->_serializer->serialize($cocktail, 'json');
        return new Response($serializedCocktail, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * Insert a new cocktail.
     *
     * @param Request $request
     * @return Response
     */
    #[Route('/cocktails', name: 'insert_cocktail', methods: ['POST'])]
    public function createCocktail(Request $request): Response
    {
        $requestData = json_decode($request->getContent(), true);
        $cocktail = new Cocktail();
        $cocktail->setName($requestData['name'] ?? null);
        $cocktail->setPrice($requestData['price'] ?? null);

        $ingredientIds = $requestData['ingredients'] ?? [];
        foreach ($ingredientIds as $ingredientId) {
            $ingredient = $this->_documentManager->getRepository(Ingredient::class)->find($ingredientId);
            if ($ingredient) {
                $cocktail->addIngredient($ingredient);
            }
        }

        $this->_documentManager->persist($cocktail);
        $this->_documentManager->flush();

        return $this->json(['id' => $cocktail->getId()]);
    }

    /**
     * Update an existing cocktail.
     *
     * @param string $id
     * @param Request $request
     * @return Response
     */
    #[Route('/cocktails/{id}', name: 'update_cocktail', methods: ['PUT'])]
    public function updateCocktail(string $id, Request $request): Response
    {
        $cocktail = $this->_documentManager->getRepository(Cocktail::class)->find($id);
        if (!$cocktail) {
            return $this->json(['error' => 'Cocktail not found'], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), true);

        $newName = $requestData['name'] ?? $cocktail->getName();
        $newPrice = $requestData['price'] ?? $cocktail->getPrice();

        if (!is_numeric($newPrice)) {
            return $this->json(['error' => 'Invalid price format'], Response::HTTP_BAD_REQUEST);
        }

        $cocktail->setName($newName);
        $cocktail->setPrice($newPrice);

        $cocktail->getIngredients()->clear();

        $ingredientIds = $requestData['ingredients'] ?? [];
        foreach ($ingredientIds as $ingredientId) {
            $ingredient = $this->_documentManager->getRepository(Ingredient::class)->find($ingredientId);
            if ($ingredient) {
                $cocktail->addIngredient($ingredient);
            }
        }

        $this->_documentManager->flush();

        return $this->json(['id' => $cocktail->getId()]);
    }

    /**
     * Delete a cocktail.
     *
     * @param string $id
     * @return Response
     */
    #[Route('/cocktails/{id}', name: 'delete_cocktail', methods: ['DELETE'])]
    public function deleteCocktail(string $id): Response
    {
        $cocktail = $this->_documentManager->getRepository(Cocktail::class)->find($id);
        if (!$cocktail) {
            return $this->json(['error' => 'Cocktail not found'], Response::HTTP_NOT_FOUND);
        }

        $this->_documentManager->remove($cocktail);
        $this->_documentManager->flush();

        return $this->json(['message' => 'Cocktail deleted successfully']);
    }
}
