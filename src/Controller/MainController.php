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


Class MainController extends Controller
{
    public function index()
    {
        $name = "Yags";

        return $this->render('front/index.html.twig', array(
            'name' => $name,
        ));

    }
}