<?php

namespace App\Tests;

use App\Entity\User;
use App\DataFixtures\AppFixtures;
use Symfony\Component\HttpFoundation\Response;

class UserTest extends AbstractTest
{
    private $serializer;

    // автоматическая загрузка фикстур
    protected function getFixtures(): array
    {
        return [AppFixtures::class];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = self::$kernel->getContainer()->get('jms_serializer');
    }

    // получение токена
    private function getToken($user)
    {
        $client = self::getClient();
        $client->request('POST','/api/v1/auth', [], [], ['CONTENT_TYPE' => 'application/json'], $user);

        return json_decode($client->getResponse()->getContent(), true)['token'];
    }

    // проверка текущего пользователя
    public function testCurrentUser() : void
    {
        $user = $this->serializer->serialize([
            'username' => 'user@study-on-billing.ru',
            'password' => 'passwordUser'
        ], 'json');

        $token = $this->getToken($user);
        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ];

        // проверка пользователя
        $client = self::getClient();
        $client->request('GET','/api/v1/current', [], [], $headers);
        $this->assertResponseOk();
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $userRepository = self::getEntityManager()->getRepository(User::class);
        $expected = $userRepository->findOneBy(['email' => $jsonResponse['username']]);
        $this->assertNotEmpty($expected);
        $this->assertEquals($expected->getRoles(), $jsonResponse['roles']);
        $this->assertEquals($expected->getBalance(), $jsonResponse['balance']);
    }

    public function testNoCurrectToken() : void
    {
        // проверка на неправильно полученный токен
        $token = 'NoCurrectToken';
        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ];

        $client = self::getClient();
        $client->request('GET','/api/v1/current', [], [], $headers);
        $this->assertResponseCode(Response::HTTP_UNAUTHORIZED);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $error = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid JWT Token', $error['message']);

        // проверка на отсутствие токена в запросе
        $headers = [
            'CONTENT_TYPE' => 'application/json',
        ];
        $client = self::getClient();
        $client->request('GET','/api/v1/current', [], [], $headers);
        $this->assertResponseCode(Response::HTTP_UNAUTHORIZED);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $error = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('JWT Token not found', $error['message']);
    }
}
