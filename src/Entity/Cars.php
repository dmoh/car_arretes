<?php
/**
 * Created by PhpStorm.
 * User: UTILISATEUR
 * Date: 27/12/2017
 * Time: 21:18
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Cars
 * @package App\Entity
 * @ORM\Entity
 * @ORM\Table(name="cars")
 * @ORM\Entity(repositoryClass="App\Repository\CarsRepository")
 * @UniqueEntity(fields="immat",
 *     message="Ce véhicule existe déjà dans la bdd."
 * )
 */

class Cars
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="immat", type="string", length=100, unique=true)
     * @Assert\NotBlank()
     */
    protected $immat;

    /**
     * @ORM\Column(name="date", type="date")
     */
    protected $date;

    /**
     * @ORM\Column(name="site", type="string", length=100)
     * @Assert\NotBlank
     * @Assert\Regex("/^\w+/")
     */
    protected $site;

    /**
     * @ORM\Column(name="num_serie", type="string", length=100)
     */
    protected $num_serie;

    /**
     * @ORM\Column(name="auteur", type="string", length=100, nullable=true)
     */
    protected $auteur;

    /**
     * @ORM\Column(name="marque", type="string", length=100, nullable=true)
     * @Assert\NotBlank()
     */
    protected $marque;

    /**
     * @ORM\Column(name="locataire", type="string", length=20)
     * @Assert\NotBlank()
     */
    protected $locataire;

    /**
     * @ORM\Column(name="date_debut_garantie", type="date", nullable=true)
     */
    protected $date_prev;

    /**
     * @ORM\Column(name="date_mar", type="date", nullable=true)
     */
    protected $date_mar;


    /**
     * @ORM\Column(name="fin_garantie", type="date", nullable=true)
     */
    protected $fin_garantie;

    /**
     * @ORM\Column(name="action_engages", type="text", nullable=true)
     */
    protected $action_engages;

    /**
     * @ORM\Column(name="etat_car", type="string", length=100)
     */
    protected $etat_car;

    /**
     * @ORM\Column(name="date_ev", type="date", nullable=true)
     */
    protected $date_ev;

    /**
     * @ORM\Column(name="date_mec", type="date", nullable=true)
     */
    protected $date_mec;


    /**
     * @ORM\Column(name="condition_garantie", type="string", nullable=true)
     *
     */
    protected $condition_garantie;


    /**
     * @ORM\Column(name="proprio_pneus", type="string", nullable=true)
     */
    protected $proprio_pneus;

    /**
     * @ORM\Column(name="p_encours", type="boolean", nullable=true)
     */
    protected $p_encours = false;

    public function __construct()
    {
        $this->date = new \Datetime();
        $this->p_encours = false;
    }


    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function setMarque($marque)
    {
         $this->marque = $marque;
    }

    /**
     * @return mixed
     */
    public function getMarque()
    {
        return $this->marque;
    }

    /**
     * @return mixed
     */
    public function getImmat()
    {
        return $this->immat;
    }

    /**
     * @param mixed $immat
     */
    public function setImmat($immat): void
    {
        $this->immat = $immat;
    }

    /**
     * @param mixed $locataire
     */
    public function setLocataire($locataire): void
    {
        $this->locataire = $locataire;
    }

    /**
     * @return mixed
     */
    public function getLocataire()
    {
        return $this->locataire;
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
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return mixed
     */
    public function getNumSerie()
    {
        return $this->num_serie;
    }

    /**
     * @param mixed $num_serie
     */
    public function setNumSerie($num_serie): void
    {
        $this->num_serie = $num_serie;
    }

    /**
     * @return mixed
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param mixed $site
     */
    public function setSite($site): void
    {
        $this->site = $site;
    }

    /**
     * @return mixed
     */
    public function getAuteur()
    {
        return $this->auteur;
    }

    /**
     * @param mixed $type
     */
    public function setAuteur($auteur): void
    {
        $this->auteur = $auteur;
    }


    /**
     * @return mixed
     */
    public function getDatePrev()
    {
        return $this->date_prev;
    }

    /**
     * @param mixed $date_prev
     */
    public function setDatePrev($date_prev): void
    {
        $this->date_prev = $date_prev;
    }

    /**
     * @return mixed
     */
    public function getDateMar()
    {
        return $this->date_mar;
    }

    /**
     * @param mixed $date_mar
     */
    public function setDateMar($date_mar): void
    {
        $this->date_mar = $date_mar;
    }


    /**
     * @return mixed
     */
    public function getFinGarantie()
    {
        return $this->fin_garantie;
    }

    /**
     * @param mixed $fin_garantie
     */
    public function setFinGarantie($fin_garantie): void
    {
        $this->fin_garantie = $fin_garantie;
    }

    /**
     * @param mixed $action_engages
     */
    public function setActionEngages($action_engages): void
    {
        $this->action_engages = $action_engages;
    }

    /**
     * @return mixed
     */
    public function getActionEngages()
    {
        return $this->action_engages;
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
    public function getEtatCar()
    {
        return $this->etat_car;
    }


    /**
     * @return mixed
     */
    public function getDateEv()
    {
        return $this->date_ev;
    }

    /**
     * @param mixed $date_ev
     */
    public function setDateEv($date_ev): void
    {
        $this->date_ev = $date_ev;
    }

    /**
     * @param mixed $date_mec
     */
    public function setDateMec($date_mec): void
    {
        $this->date_mec = $date_mec;
    }

    /**
     * @return mixed
     */
    public function getDateMec()
    {
        return $this->date_mec;
    }

    /**
     * @param mixed $duree_garantie
     */
    public function setConditionGarantie($duree_garantie): void
    {
        $this->condition_garantie = $duree_garantie;
    }

    /**
     * @return mixed
     */
    public function getConditionGarantie()
    {
        return $this->condition_garantie;
    }

    /**
     * @param mixed $proprio_pneus
     */
    public function setProprioPneus($proprio_pneus): void
    {
        $this->proprio_pneus = $proprio_pneus;
    }

    /**
     * @return mixed
     */
    public function getProprioPneus()
    {
        return $this->proprio_pneus;
    }

    /**
     * @return mixed
     */
    public function getPEncours()
    {
        return $this->p_encours;
    }

    /**
     * @param mixed $p_encours
     */
    public function setPEncours($p_encours): void
    {
        $this->p_encours = $p_encours;
    }


    public function __toString()
    {
       return  $this->immat;
    }


}