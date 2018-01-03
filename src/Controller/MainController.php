<?php
/**
 * Created by PhpStorm.
 * User: Service Info
 * Date: 27/12/2017
 * Time: 15:43
 */
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Form\CarType;
use App\Entity\Cars;


Class MainController extends Controller
{
    public function index(Request $req)
    {
        $em = $this->getDoctrine()->getManager();

        $car = new Cars;

        $form = $this->createForm(CarType::class, $car);

        if($req->isMethod('POST'))
        {

            $form->handleRequest($req);
            $data = $form->getData();



            $repository = $this->getDoctrine()->getRepository(Cars::class);
            $car_R = $repository->findOneBy(['immat' => $data->getImmat()]);
            //$test = $data->getImmat();
             if($car_R == NULL)
             {
                 if($form->isValid()){


                     $em -> persist($car);
                     $em -> flush();
                     $this->addFlash('info', 'Oui oui, il est bien enregistré !');


                    return $this->redirectToRoute('consultation');
                 }

             }
             else
             {
                 $this->addFlash('info', 'Ce véhicule existe déjà dans la base');
                 return $this->render('front/index.html.twig', array(
                     'form' => $form->createView(),
                 ));
             }




        }

        /*recuperation de l'entity manager
        $em = $this->getDoctrine()->getManager();

        $em->persist($car);
        $em->flush();*/

        return $this->render('front/index.html.twig', array(
            'form' => $form->createView(),
        ));

    }


    public function consultation(Request $request)
    {
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository(Cars::class);
        $car_listing = $repository->findBy(
            array(), array('date' => "Desc")
        );

        /*var_dump($car_listing);
        die();*/

        return $this->render('front/consultation.html.twig',
            array('Cars' => $car_listing)
            );
    }

    public function car($id)
    {
        $repo = $this->getDoctrine()
            ->getManager()
            ->getRepository(Cars::class);
        $car_info = $repo->find(
           $id
        );

        return $this->render('front/car.html.twig',
            array('car' => $car_info)
        );

    }
}