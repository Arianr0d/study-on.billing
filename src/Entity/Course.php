<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CourseRepository::class)
 */
class Course
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $codeCourse;

    /**
     * @ORM\Column(type="smallint")
     */
    private $typeCourse;

    /**
     * @ORM\Column(type="float")
     */
    private $costCourse;

    /**
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="course")
     */
    private $transactions;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeCourse(): ?string
    {
        return $this->codeCourse;
    }

    public function setCodeCourse(string $codeCourse): self
    {
        $this->codeCourse = $codeCourse;

        return $this;
    }

    public function getTypeCourse(): ?string
    {
        $type = 'free';
        if ($this->typeCourse == 1) $type = 'rent';
        if ($this->typeCourse == 2) $type = 'full';
        return $type;
    }

    public function setTypeCourse(int $typeCourse): self
    {
        $this->typeCourse = $typeCourse;

        return $this;
    }

    public function getCostCourse(): ?float
    {
        return $this->costCourse;
    }

    public function setCostCourse(?string $costCourse): self
    {
        $this->costCourse = $costCourse;

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setCourse($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getCourse() === $this) {
                $transaction->setCourse(null);
            }
        }

        return $this;
    }
}