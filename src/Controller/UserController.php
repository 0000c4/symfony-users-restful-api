<?php

namespace App\Controller;

use App\DTO\UserDTO;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/api/users')]
class UserController extends AbstractController
{
    public function __construct(
        private UserService $userService,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'api_get_users', methods: ['GET'])]
    public function getUsers(): JsonResponse
    {
        $users = $this->userService->getAllUsers();
        
        return $this->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    #[Route('', name: 'api_create_user', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        try {
            $userDTO = $this->serializer->deserialize(
                $request->getContent(),
                UserDTO::class,
                'json'
            );

            $violations = $this->validator->validate($userDTO);
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }
                
                return $this->json([
                    'status' => 'error', 
                    'message' => 'Validation failed',
                    'errors' => $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = $this->userService->createUser($userDTO);
            
            return $this->json([
                'status' => 'success',
                'data' => $user
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'api_delete_user', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse
    {
        try {
            $this->userService->deleteUser($id);
            
            return $this->json([
                'status' => 'success',
                'message' => 'User deleted successfully'
            ]);
        } catch (NotFoundHttpException $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}