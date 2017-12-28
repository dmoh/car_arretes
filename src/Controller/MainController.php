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
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Cars;


Class MainController extends Controller
{
    public function index(Request $req)
    {
        $car = new Cars;

        $car->setImmat("BB-345-DD");
        $car->setMarque("Mercedes");
        $car->setProprio("Aps");
        $car->setNumParc(9685);
        $car->setSite("La Roche sur Foron");
        $car->setNumSerie("WNNNNNNNSDSDN2323");
        $car->setTypecar("Crossway");
        $car->setTypePanne("C'est une panne dû au filtre a huile qui n'est pas très importante...");





        //recuperation de l'entity manager
        $em = $this->getDoctrine()->getManager();

        $em->persist($car);
        $em->flush();







        $name = "Yags";

        return $this->render('front/index.html.twig', array(
            'name' => $name,
        ));

    }
}