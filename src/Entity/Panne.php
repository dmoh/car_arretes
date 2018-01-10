<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="panne")
 * @ORM\Entity(repositoryClass="App\Repository\PanneRepository")
 */
class Panne
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="date", type="date")
     */
    protected $date;

    /**
     * @ORM\Column(name="auteur", type="string", length=100)
     */
    protected $auteur;

    /**
     * @ORM\Column(name="etat_car", type="string", length=100)
     */
    protected $etat_car;

    /**
     * @ORM\Column(name="desc_panne", type="text")
     */
    protected $desc_panne;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Cars")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cars;

    /**
     * @return mixed
     */
    public function getCars(): Cars
    {
        return $this->cars;
    }

    /**
     * @param mixed $cars
     */
    public function setCars(Cars $cars): void
    {
        $this->cars = $cars;
    }



    public function __construct()
    {
        $this->date = new  \DateTime();
    }

    /**
     * @return mixed
     */
    public function getEtatCar()
    {
        return $this->etat_car;
    }

    /**
     * @param mixed $etat_car
     */
    public function setEtatCar($etat_car): void
    {
        $this->etat_car = $etat_car;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date): void
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getAuteur()
    {
        return $this->auteur;
    }

    /**
     * @param mixed $auteur
     */
    public function setAuteur($auteur): void
    {
        $this->auteur = $auteur;
    }

    /**
     * @return mixed
     */
    public function getDescPanne()
    {
        return $this->desc_panne;
    }

    /**
     * @param mixed $desc_panne
     */
    public function setDescPanne($desc_panne): void
    {
        $this->desc_panne = $desc_panne;
    }




}
