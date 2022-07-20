<?php

namespace App\Controller;
use App\DTO\UserDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializerBuilder;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Symfony\Component\Security\Core\Security;
use Nelmio\ApiDocBundle\Annotation\Security as NelmioSecurity;


class ApiController extends AbstractController
{
    /**
     * * @OA\Post(
     *     path="/api/v1/auth",
     *     description="Аутентификация пользователя"
     * )
     * @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *          @OA\Property(
     *          property="username",
     *          type="string",
     *          example="user@study-on-billing.ru"
     *          ),
     *          @OA\Property(
     *          property="password",
     *          type="string",
     *          example="passwordUser"
     *          )
     *      )
     * )
     * @OA\Response(
     *     response="200",
     *     description="Авторизация прошла успешно",
     *     @OA\JsonContent(
     *          @OA\Property(
     *          property="code",
     *          type="string",
     *          )
     *      )
     * )
     * @OA\Response(
     *     response="401",
     *     description="Ошибка авторизации",
     *     @OA\JsonContent(
     *          @OA\Property(
     *          property="code",
     *          type="string",
     *          ),
     *          @OA\Property(
     *          property="message",
     *          type="string",
     *          )
     *      )
     * )
     * @OA\Response(
     *     response="default",
     *     description="Непредвиденная ошибка",
     *     @OA\JsonContent(
     *          @OA\Property(
     *          property="code",
     *          type="string",
     *          ),
     *          @OA\Property(
     *          property="message",
     *          type="string",
     *          )
     *      )
     * )
     * @Route("/api/v1/auth", name="app_login_api")
     */
    public function login(): void
    {
        //auth
    }

    /**
     * *  @OA\Post(
     *     path="/api/v1/register",
     *     description="Регистрация пользователя"
     * )
     * @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *          @OA\Property(
     *          property="username",
     *          type="string",
     *          example="newuser@study-on-billing.ru"
     *          ),
     *          @OA\Property(
     *          property="password",
     *          type="string",
     *          example="randompassword"
     *          )
     *      )
     * )
     * @OA\Response(
     *     response="201",
     *     description="Регистрация пользователя прошла успешно",
     *     @OA\JsonContent(
     *          @OA\Property(
     *          property="token",
     *          type="string",
     *          ),
     *          @OA\Property(
     *          property="roles",
     *          type="array",
     *          @OA\Items(type="string")
     *          )
     *      )
     * )
     * @OA\Response(
     *     response="400",
     *     description="Ошибка валидации",
     *     @OA\JsonContent(
     *          @OA\Property(
     *          property="errors",
     *          type="array",
     *              @OA\Items(
     *                  @OA\Property(
     *                      type="string",
     *                      property="property_name"
     *                  )
     *              )
     *          )
     *      )
     * )
     * @OA\Response(
     *     response="401",
     *     description="Ошибка аутентификации пользователя",
     *     @OA\JsonContent(
     *          @OA\Property(
     *          property="code",
     *          type="string",
     *          ),
     *          @OA\Property(
     *          property="message",
     *          type="string",
     *          )
     *      )
     * )
     * @OA\Response(
     *     response="default",
     *     description="Непредвиденная ошибка",
     *     @OA\JsonContent(
     *          @OA\Property(
     *          property="code",
     *          type="string",
     *          ),
     *          @OA\Property(
     *          property="message",
     *          type="string",
     *          )
     *      )
     * )
    * @Route("/api/v1/register", name="api_register", methods={"POST"})
    */
    public function register(JWTTokenManagerInterface $JWTTokenManager,
                             Request $request,
                             ValidatorInterface $validator,
                             EntityManagerInterface $manager,
                             UserPasswordHasherInterface $passwordHasher,
                             UserRepository $userRepository): Response
    {
        $serializer = SerializerBuilder::create()->build();
        $userDto = $serializer->deserialize($request->getContent(), UserDTO::class, 'json');
        $errors = $validator->validate($userDto);

        // заполнение массива ошибками
        $jsonErrors = array();
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $jsonErrors[$error->getPropertyPath()][] = $error->getMessage();
            }
            return $this->json(['errors' => $jsonErrors], Response::HTTP_BAD_REQUEST);
        }
        // проверка на существование пользователя с указанной почтой
        if ($userRepository->findOneBy(['email' => $userDto->getUserName()])) {
            return $this->json(['error' => 'Пользователь ' . $userDto->getUserName() . 'уже существует'],
                Response::HTTP_BAD_REQUEST);
        }

        $user = User::fromDTO($userDto, $passwordHasher);
        $manager->persist($user);
        $manager->flush();
        $token = $JWTTokenManager->create($user);
        return $this->json(['token' => $token, 'roles' => $user->getRoles()], Response::HTTP_CREATED);
    }

    /**
     * *  @OA\Get(
     *     path="api/v1/current",
     *     description="Получение пользователя",
     * )
     * @OA\Response(
     *     response=200,
     *     description="Возвращение информации о пользователе",
     *     @OA\JsonContent(
     *        @OA\Property(
     *          property="username",
     *          type="string",
     *        ),
     *        @OA\Property(
     *          property="roles",
     *          type="array",
     *          @OA\Items(type="string")
     *        ),
     *        @OA\Property(
     *          property="balance",
     *          type="float",
     *        )
     *     )
     * )
     * @OA\Response(
     *     response=401,
     *     description="Пользователь не был авторизирован",
     *     @OA\JsonContent(
     *        @OA\Property(
     *          property="code",
     *          type="string"
     *        ),
     *        @OA\Property(
     *          property="message",
     *          type="string"
     *        ),
     *     )
     * )
     * @OA\Response(
     *     response="default",
     *     description="Непредвиденная ошибка",
     *     @OA\JsonContent(
     *        @OA\Property(
     *          property="code",
     *          type="string"
     *        ),
     *        @OA\Property(
     *          property="message",
     *          type="string"
     *        ),
     *     )
     * )
    * @\Nelmio\ApiDocBundle\Annotation\Security(name="Bearer")
    * @Route("/api/v1/current", name="api_current", methods={"GET"})
    */
    public function current(Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            return $this->json([
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'message' => 'Пользователь не авторизован'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json(
            ['username' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'balance' => $user->getBalance(),
            ],
            Response::HTTP_OK
        );
    }
}