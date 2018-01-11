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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Form\CarType;
use App\Entity\Cars;
use App\Entity\Panne;
use App\Form\PanneType;

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
                     //$this->addFlash('info', 'Oui oui, il est bien enregistré !');
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
            ;
        $car_info = $repo->getRepository(Cars::class)->find(
           $id
        );

        $pannes = $repo->getRepository(Panne::class)
                 ->findBy(array('cars' => $car_info));


        return $this->render('front/car.html.twig',
            array(
                'car'       => $car_info,
                'pannes'    => $pannes
                )
        );

    }


    public function panne(Request $req, $id)
    {
        //enntity manager
        $em = $this->getDoctrine()->getManager();

        //repository
        $repo = $this->getDoctrine()->getManager();

        $panne = new Panne;
        $form = $this->createForm(PanneType::class, $panne);
        $car = $repo->getRepository(Cars::class)->find($id);


        if($req->isMethod('POST'))
        {
            $form->handleRequest($req);

            if($form->isValid()){

               // $this->addFlash('info', 'Oui oui, il est bien enregistré !');
                $panne->setCars($car);
                $em -> persist($car);
                $em -> persist($panne);
                $em -> flush();

                return $this->redirectToRoute('consultation');
            }

        }
        return $this->render('front/edit.html.twig',
            array('car' => $car,
                  'form' => $form->createView(),
                )
            );

    }



    public function recherche(Request $req)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();
        $car_trouver = false;
        $form = $this->createFormBuilder()
                ->add('recherche', TextType::class)
                ->add('Rechercher', SubmitType::class)
                ->getForm()
        ;
        //ajax 
        if ($req->isXmlHttpRequest()) { 
        }

         if( $req->isMethod('POST') )
        {
            $form->handleRequest($req);
            $immat_car = $form->getData();
            $imm = implode($immat_car);
            $car_trouver= $em->getRepository(Cars::class)->findOneByImmat(
            $imm
            );
           if(!$car_trouver)
           {
                $imm = strtolower($imm);
                $qb = $em->createQueryBuilder();
                $car_trouver = $qb->select('c')->from('App\Entity\Cars', 'c')
                  ->where( $qb->expr()->like('c.immat', $qb->expr()->literal('%' . $imm . '%')) )
                  ->setMaxResults(5)
                  ->getQuery()
                  ->getResult();
                  


                $form = $this->createFormBuilder()
                        ->add('recherche', TextType::class)
                        ->add('Rechercher', SubmitType::class)
                        ->getForm();

                return $this->render('front/recherche.html.twig', array(
                    'form' => $form->createView(),
                    'car'  => $car_trouver,
                    'plusieurs' => 'OK', 
                ));
            }
            $form = $this->createFormBuilder()
                    ->add('recherche', TextType::class)
                    ->add('Rechercher', SubmitType::class)
                    ->getForm()
            ;
            return $this->render('front/recherche.html.twig', array(
                'form' => $form->createView(),
                'car'  => $car_trouver,  
            )); 
        }//end if post

        //$repo = $em->getRepository(Cars::class)->find($id);
    return $this->render('front/recherche.html.twig', array(
        'form' => $form->createView(),
        'car'  => $car_trouver
    ));
    }

    public function listePanne(Request $request)
    {
        return $this->render('front/listep.html.twig');
    }
}