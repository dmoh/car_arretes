<?php
/**
 * Created by PhpStorm.
 * User: UTILISATEUR
 * Date: 27/12/2017
 * Time: 21:18
 */

namespace App\Entity;


class Cars
{
    protected $id;

    protected $immat;

    protected $date;

    protected $marque;

    protected $proprio;




    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id = $id;
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
     * @param mixed $proprio
     */
    public function setProprio($proprio): void
    {
        $this->proprio = $proprio;
    }

    /**
     * @return mixed
     */
    public function getProprio()
    {
        return $this->proprio;
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



}