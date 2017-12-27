<?php
/**
 * Created by PhpStorm.
 * User: Service Info
 * Date: 27/12/2017
 * Time: 15:43
 */
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Cars;


Class MainController extends Controller
{
    public function index()
    {
        $car = new Cars;

        $car->setImmat("BB-345-DD");
        $car->setMarque("Mercedes");
        $car->setProprio("Aps");

        


        $name = "Yags";

        return $this->render('front/index.html.twig', array(
            'name' => $name,
        ));

    }
}