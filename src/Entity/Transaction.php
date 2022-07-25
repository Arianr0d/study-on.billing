<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TransactionRepository::class)
 */
class Transaction
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="transactions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userBilling;

    /**
     * @ORM\ManyToOne(targetEntity=Course::class, inversedBy="transactions")
     */
    private $Course;

    /**
     * @ORM\Column(type="smallint")
     */
    private $typeOperation;

    /**
     * @ORM\Column(type="float")
     */
    private $value;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateTimeTransaction;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateTimeEndTransaction;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserBilling(): ?User
    {
        return $this->userBilling;
    }

    public function setUserBilling(?User $userBilling): self
    {
        $this->userBilling = $userBilling;

        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->Course;
    }

    public function setCourse(?Course $Course): self
    {
        $this->Course = $Course;

        return $this;
    }

    public function getTypeOperation(): ?string
    {
        $type = 'payment';
        if ($this->typeOperation == 1) $type = 'deposit';
        return $type;
    }

    public function setTypeOperation(int $typeOperation): self
    {
        $this->typeOperation = $typeOperation;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getDateTimeTransaction(): ?\DateTimeInterface
    {
        return $this->dateTimeTransaction;
    }

    public function setDateTimeTransaction(\DateTimeInterface $dateTimeTransaction): self
    {
        $this->dateTimeTransaction = $dateTimeTransaction;

        return $this;
    }

    public function getDateTimeEndTransaction(): ?\DateTimeInterface
    {
        return $this->dateTimeEndTransaction;
    }

    public function setDateTimeEndTransaction(?\DateTimeInterface $dateTimeEndTransaction): self
    {
        $this->dateTimeEndTransaction = $dateTimeEndTransaction;

        return $this;
    }
}