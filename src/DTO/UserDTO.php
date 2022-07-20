<?php

namespace App\DTO;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class UserDTO
{
    /**
     * @var string
     *
     * @Assert\NotBlank(message="Name is mandatory")
     * @Assert\Email(message="Invalid email address")
     * @Serializer\Type("string")
     */
    private $username;


    /**
     * @var string
     *
     * @Assert\NotBlank(message="Password is mandatory")
     * @Assert\Length(min=6, minMessage="Password must be longer than 6 characters")
     * @Serializer\Type("string")
     */
    private $password;


    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return self
     */
    public function setUserName(string $username): UserDTO
    {
        $this->username = $username;
        return $this;
    }


    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return self
     */
    public function setPassword(string $password): UserDTO
    {
        $this->password = $password;
        return $this;
    }
}