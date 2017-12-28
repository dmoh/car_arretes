<?php
/**
 * Created by PhpStorm.
 * User: UTILISATEUR
 * Date: 27/12/2017
 * Time: 21:18
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Cars
 * @package App\Entity
 * @ORM\Entity
 * @ORM\Table(name="cars")
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
     * @ORM\Column(name="immat", type="string", length=100)
     */
    protected $immat;

    /**
     * @ORM\Column(name="date", type="date")
     */
    protected $date;


    /**
     * @ORM\Column(name="num_parc", type="integer")
     */
    protected $num_parc;

    /**
     * @ORM\Column(name="site", type="string", length=100)
     */
    protected $site;

    /**
     * @ORM\Column(name="num_serie", type="string", length=100)
     */
    protected $num_serie;

    /**
     * @ORM\Column(name="typecar", type="string", length=100)
     */
    protected $typecar;

    /**
     * @ORM\Column(name="marque", type="string", length=100)
     */
    protected $marque;

    /**
     * @ORM\Column(name="locataire", type="string", length=20)
     */
    protected $locataire;

    /**
     * @ORM\Column(name="type_panne", type="text")
     */
    protected $type_panne;

    /**
     * @ORM\Column(name="date_prev", type="date", nullable=true)
     */
    protected $date_prev;

    /**
     * @ORM\Column(name="date_mar", type="date", nullable=true)
     */
    protected $date_mar;

    /**
     * @ORM\Column(name="deb_garantie", type="string", length=100, nullable=true)
     */
    protected $deb_garantie;

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
     * @ORM\Column(name="duree_garantie", type="string", nullable=true)
     *
     */
    protected $duree_garantie;


    /**
     * @ORM\Column(name="proprio_pneus", type="string", nullable=true)
     */
    protected $proprio_pneus;




    public function __construct()
    {
        $this->date = new \Datetime();
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
    public function getNumParc()
    {
        return $this->num_parc;
    }

    /**
     * @param mixed $num_parc
     */
    public function setNumParc($num_parc): void
    {
        $this->num_parc = $num_parc;
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
    public function getTypecar()
    {
        return $this->typecar;
    }

    /**
     * @param mixed $type
     */
    public function setTypecar($typecar): void
    {
        $this->typecar = $typecar;
    }

    public function getTypePanne()
    {
        return $this->type_panne;
    }

    /**
     * @param mixed $type_panne
     */
    public function setTypePanne($type_panne): void
    {
        $this->type_panne = $type_panne;
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
     * @param mixed $deb_garantie
     */
    public function setDebGarantie($deb_garantie): void
    {
        $this->deb_garantie = $deb_garantie;
    }

    /**
     * @return mixed
     */
    public function getDebGarantie()
    {
        return $this->deb_garantie;
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
    public function setDureeGarantie($duree_garantie): void
    {
        $this->duree_garantie = $duree_garantie;
    }

    /**
     * @return mixed
     */
    public function getDureeGarantie()
    {
        return $this->duree_garantie;
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



}