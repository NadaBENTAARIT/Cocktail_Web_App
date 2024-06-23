<?php

namespace App\Controller;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; 
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
class UserController extends AbstractController
{
    private $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }





        /* ______________ Register  ______________ */


    #[Route('/Register', name: 'User_create', methods: ['POST'])]
    public function UserCreate(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse // Updated type hint
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'];
        $password = $data['password'];

        $emailExist = $this->documentManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($emailExist) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Cet email existe déjà, veuillez le changer'
            ]);
        } else {
            $user= new User();
            $user->setEmail($email)
                ->setPassword($passwordHasher->hashPassword($user, $password)); 

            $this->documentManager->persist($user);
            $this->documentManager->flush();

            return new JsonResponse([
                'status' => true,
                'message' => 'L\'utilisateur créé avec succès'
            ]);
        }
    }


/* ______________ Login  ______________ */

    #[Route('/login', name: 'login', methods: ['POST'])]
public function login(Request $request, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    $email = $data['email'];
    $password = $data['password'];

    $user= $this->documentManager->getRepository(User::class)->findOneBy(['email' => $email]);

    if (!$user) {
        throw $this->createNotFoundException('Utilisateur non trouvé.');
    }

    if (!$passwordHasher->isPasswordValid($user, $password)) {
        throw new BadCredentialsException('Mot de passe incorrect.');
    }

    $token = $jwtManager->create($user);


    return new JsonResponse([
        'status' => true,
        'message' => 'Connexion réussie',
        'token' => $token,
    ]);
}


/*

    #[Route('/api/getAllChiefs', name: 'get_allChiefs', methods: ['GET'])]
    public function getAllChiefs(): JsonResponse
    {
        $Chiefs = $this->documentManager->getRepository(Chief::class)->findAll();

        return $this->json($Chiefs, 200, [], ['groups' => 'api']);
    }


    public function generateToken(JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        $user = $this->getUser(); // Récupérez l'utilisateur actuel (vous devez être authentifié)
        
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        // Générez le token
        $token = $jwtManager->create($user);

        return new JsonResponse(['token' => $token]);
    }*/
}

