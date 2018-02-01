<?php
/**
 * Created by PhpStorm.
 * User: Service Info
 * Date: 27/12/2017
 * Time: 15:43
 */
namespace App\Controller;

use App\Repository\PanneRepository;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Repository\CarsRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;


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
        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT c FROM App\Entity\Cars c";
        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator-> paginate(
          $query,
          $request->query->getInt('page', 1),5
        );

        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository(Cars::class);
        $car_listing = $repository->findBy(array(),
             array('date' => 'DESC')
        );

        /*var_dump($car_listing);
        die();*/

        return $this->render('front/consultation.html.twig',
            //array('Cars' => $car_listing)
            array(
                'pagination'=> $pagination,
                'Cars' => $car_listing
            )
            );
    }


    //Historique du Car
    public function car($id)
    {
        $repo = $this   ->getDoctrine()
                        ->getManager()
            ;

        $car_info = $repo->getRepository(Cars::class)->find(
           $id
        );


        $pannes = $repo->getRepository(Panne::class)
                 ->findBy(array(
                     'cars' => $car_info),
                     array('id' => 'DESC',
                 ));
        $nombrep = count($pannes);
        $car_info->setCompteurPanne($nombrep);

        return $this->render('front/car.html.twig',
            array(
                'car'       => $car_info,
                'nombrep'   =>  $nombrep,
                'pannes'    => $pannes
                )
        );

    }


    public function addPanne(Request $req, $id)
    {
        //enntity manager
        $em = $this->getDoctrine()->getManager();

        //repository
        $repo = $this->getDoctrine()->getManager();

        $panne = new Panne;
        $form = $this->createForm(PanneType::class, $panne);
        $car = $repo->getRepository(Cars::class)->find($id);



        /*$liste_panne =  $em->getRepository(Panne::class)
                        ->findBy(array('cars_id', $car->getID()));


        if($liste_panne)
        {
            return $this->render('front/edit.html.twig',
                array('car' => $car,
                    'form' => $form->createView(),
                    'liste_panne'=> $liste_panne
                )
            );
        }*/


        if($req->isMethod('POST'))
        {
            $form->handleRequest($req);

            if($form->isValid()){

                if(!$panne->getDateDebPanne())
                {
                    $panne->setDateDebPanne(new \DateTime());
                }
               // $this->addFlash('info', 'Oui oui, il est bien enregistré !');

                $test = $form->getData();


                $etat_car=$test->getEtatCar();

                if($etat_car == 'roulant_ano' || $etat_car == 'roulant ano')
                {
                    $panne->setNaturePanne($test->getNaturePanne());
                    $panne->setDescPanneAnoP($test->getDescPanne());
                    $panne->setAuteur($test->getAuteur());

                    $car->setAuteur($test->getAuteur());
                    $car->setNaturePanneCar($test->getNaturePanne());
                    $car->setDescPanneAno($test->getDescPanne());
                    $car->setDescPanneCar($test->getDescPanne());

                }

                $panne->setCars($car);

                // recup la date de début
                $car->setDatePanneDeb(new \DateTime());
                $car->setAuteur($test->getAuteur());

                $date_prev = $test->getDatePrev();
                $date_effective = $test->getDateEffective();

                if( $date_effective != null  && $date_prev != null)
                {
                    // Durée de panne
                    $duree_panne_prev = date_diff($date_prev, $date_effective);
                    $duree_panne_prev = $duree_panne_prev->format('%d');

                    $panne->setDureePannePrev($duree_panne_prev);

                }


                //On ajoute une panne au compteur
                $c = (int) $car->getCompteurPanne();
                $car->setCompteurPanne($c++);

                //Met à jour la desc de la panne actuelle
                $desc_panne_car= $test->getDescPanne();
                $car->setDescPanneCar($desc_panne_car);
                $car->setNaturePanneCar($test->getNaturePanne());

                //Mettre à jour l'etat du car
                $car->setEtatCar($test->getEtatCar());

                //mettre à jour date de modif
                $car->setDateMaj(new \DateTime());


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
        $car_trouver = 'rien';
        $form = $this->createFormBuilder()
                ->add('recherche', TextType::class)
                ->add('Rechercher', SubmitType::class)
                ->getForm()
        ;
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository(Cars::class);
        $car_listing = $repository->findBy(array(),
            array('date' => 'DESC')
        );
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
                    'car' => 'trouver',
                    'car_listing'  => $car_trouver,
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
                'plusieurs' => 'rien',
            )); 
        }//end if post



        //$repo = $em->getRepository(Cars::class)->find($id);
        return $this->render('front/recherche.html.twig', array(
            'form' => $form->createView(),
            'car'  => $car_trouver,
            'car_listing' => $car_listing,
            'plusieurs' => 'debut',
        ));
    }

    public function listePanne(Request $request)
    {
        $em = $this->getDoctrine()->getManager();


        $list_panne = $em->getRepository(Cars::class)->findBy(
          ['etat_car'  => 'panne' ],
          ['date'       => 'ASC']
        );

        $list_panne_ano = $em->getRepository(Cars::class)->findBy(
          ['etat_car'   =>  'roulant_ano'],
          ['date_maj'   =>  'ASC']
        );




        if(!$list_panne)
        {
            $list_panne = array();
        }

        if(!$list_panne_ano)
        {
            $list_panne_ano = array();
        }


        if ($request->isMethod('POST'))
        {


        }


        //ajax
        if ($request->isXmlHttpRequest()) {




        }
        //var_dump($list_panne);

           $form = $this->createFormBuilder()
            ->add('etat_car', ChoiceType::class,array(
                'choices' => array(
                'NON' => "non",
                'OUI' => "oui",
                ),
                'choice_attr' => function($val, $key, $index) {
                    // adds a class like attending_yes, attending_no, etc
                    return ['class' => 'attending_'.strtolower($key)];
                },))
            ->add('enregistrer', SubmitType::class)
            ->getForm();


        /*$options = $form->get('id')->getConfig()->getOptions();
        $choices = $options['choice_list']->getChoices();*/




        return $this->render('front/listep.html.twig', array(
            'liste_panne' =>    $list_panne,
            'liste_panne_ano' => $list_panne_ano,
            'form'        =>    $form->createView(),
        ));
    }


    public function editCar(Request $request, $id){
        $em = $this->getDoctrine()->getManager();

        $car = $em->getRepository(Cars::class)->find($id);
        $panne = new Panne($car);




        $etat_car  = strtolower($car->getEtatCar());

        //Edition d'un autocar
        $form = $this->get('form.factory')->create(CarType::class, $car);

        if(null === $form)
        {
            throw new NotFoundHttpException("Cet autocar n'existe pas.");
        }

        if($request->isMethod('POST') && $form->handleRequest($request)->isValid())
        {
            $rep = $form->getData();



            if ($rep->getEtatCar() == "panne" || $rep->getEtatCar() == "PANNE" && $etat_car != "PANNE" || $etat_car != "panne")
            {
                $car->setDatePanneDeb(new \DateTime());

                if(!$panne->getDateDebPanne())
                {
                    $panne->setDateDebPanne(new \DateTime());
                }


            }
            elseif ($etat_car == "panne" && $rep->getEtatCar() == "roulant")
            {
                $date1 = $car->getDatePanneDeb();


                if($date1 != null)
                {
                    //$date1 = new \DateTime($date1);

                    $date2 = new \DateTime();


                    if(!$rep->getDateFinGarantie())
                    {
                        /*$car->setDatePanneFin($date2);

                        $duree_panne = date_diff($date2, $date1);

                        $duree_panne = $duree_panne->format('%h:%i');

                        $car->setDureePanne($duree_panne);*/
                    }




                }

            }
            $car->setAuteur($rep->getAuteur());
            //$rep->setDate(new \DateTime());
            $car->setEtatCar($rep->getEtatCar());
            $em->persist($car);
            $em->flush();

            return $this->redirectToRoute('consultation');
            /*var_dump($rep);
            die();*/
        }
        /*var_dump($form);
        die();*/
        return $this->render('front/editcar.html.twig',
                array('car' => $car,
                      'form' => $form->createView(),
                )
        );
    }


    public function deleteCar($id)
    {
        $em = $this->getDoctrine()->getManager();
        $car = $em->getRepository(Cars::class)->find($id);


        if(null === $car)
        {
            throw new NotFoundHttpException("Cet autocar n'existe pas !");
        }


        // On récupère le car avec ses pannes
        foreach ($car->getPannes()as $panne){
            $car->removePanne($panne);
        }
       $em->remove($car);
        $em->flush();
        return $this->redirectToRoute('consultation');
    }





    public function editPanne(Request $req, $id)
    {
        $em= $this->getDoctrine()->getManager();

        $car = $em->getRepository(Cars::class)->findOneBy(array('id' => $id));

        $etat_actu = $car->getEtatCar();


        $panne = new Panne($car);

        //$list= $em->getRepository(Panne::class)->getCarWithLastPanne($car->getId());
        $liste_panne = $em->getRepository(Panne::class)->findOneBy(
            ['cars'     => $car ],
            ['id'       => 'DESC']
        );

        $etat_panne = $liste_panne->getEtatCar();






        $liste_panne_anterieur = $em->getRepository(Panne::class)->findBy(
          ['cars'   =>  $car],
          ['id'   => 'ASC']
        );
       // $liste_panne= $em->getRepository(Panne::class)->findBy(array('cars' => $car));
        //$test =$liste_panne->getPannes();




        //Edition d'un autocar
        $form = $this->get('form.factory')->create(PanneType::class, $liste_panne);

        if ($req->isMethod('POST'))
        {
            $form->handleRequest($req);
            if($form->isValid())
            {
                $test = $form->getData();

                $etat_car = $test->getEtatCar();

                if( $etat_car == 'panne')
                {
                    $car->setEtatCar($etat_car);
                    $car->setDateMaj(new \DateTime());
                    $car->setAuteur($test->getAuteur());


                    $panne->setEtatCar($etat_car);
                    $panne->setAuteur($test->getAuteur());
                    $panne->setDatePrev($test->getDatePrev());
                    $panne->setDescPanne($test->getDescPanne());
                    $panne->setSuitesDonnes($test->getSuitesDonnes());



                    $car->setNaturePanneCar($test->getNaturePanne());
                    $car->setDatePanneDeb($test->getDatePrev());
                    $car->setDescPanneCar($test->getDescPanne());
                    $car->setDescPanneCar($test->getDescPanne());
                    $idcar = $car->getId();





                    $panne->setCars($car);

                    if($test->getDateEffective())
                    {
                        //Date_ <-> DateFin de panne
                        $car->setDateFinPanne($test->getDateEffective());
                        $panne->setDateEffective($test->getDateEffective());
                    }

                    /*$em->persist($car);
                    $em->persist($panne);*/
                    $em->flush();

                    return $this->redirectToRoute('car', array('id'=>$idcar));



                }
                elseif ($etat_car == 'roulant ano' || $etat_car  == 'roulant_ano' && $etat_actu == 'roulant_ano')
                {
                    //Roulant avec anomalie écrase panne

                    $car->setEtatCar($etat_actu);
                    $car->setDateMaj(new \DateTime());


                    $panne->setEtatCar($etat_car);
                    $panne->setAuteur($test->getAuteur());
                    $panne->setDatePrev($test->getDatePrev());

                    $car->setNaturePanneCar($test->getNaturePanne());
                    $panne->setNaturePanne($test->getNaturePanne());
                    $car->setAuteur($test->getAuteur());
                    $car->setDatePanneDeb($test->getDatePrev());

                   // $car->setPannes($panne);
                    $panne->setCars($car);


                    //Desc panne ano dans l'entitè Car
                    $car->setDescPanneAno($test->getDescPanne());

                    $panne->setDescPanneAnoP($test->getDescPanne());
                    //$car->setDescPanneAno($test->getDescPanne());


                    if($test->getDateEffective())
                    {
                        //Date_ <-> DateFin de panne
                        $car->setDateFinPanne($test->getDateEffective());
                        $panne->setDateEffective($test->getDateEffective());

                        if ($test->getDatePrev() && $test->getDateEffective())
                        {
                            $deb_panne = $test->getDatePrev();
                            $fin_panne = $test->getDateEffective();
                            $duree_panne = date_diff($deb_panne, $fin_panne);

                            $duree_panne = $duree_panne->format('%d');

                            $panne->setDureePannePrev($duree_panne);
                        }

                    }

                    $id_car = $car->getId();

                    /*$em->persist($car);
                    $em->persist($panne);*/
                    $em->flush();


                    return $this->redirectToRoute('car',array(
                        'id' => $id_car
                    ));
                }
                elseif ($etat_car == 'panne' && $etat_actu == 'panne')
                {
                    $panne->setCars($car);
                    $p = $em->getRepository(Panne::class)->find($test->getId());
                    $car->setNaturePanneCar($test->getNaturePanne());

                    $em->persist($p);
                    $em->flush();

                   return $this->redirectToRoute('consultation');
                    //modifie la panne en cour

                }
                elseif ($etat_car == 'roulant')
                {



                    $liste_panne->setEtatCar($etat_actu);
                    /*$panne->setAuteur($test->getAuteur());

                    $panne->setDatePrev($test->getDatePrev());
                    $panne->setDateEffective($test->getDateEffective());
                    $panne->setDescPanne($test->getDescPanne());
                    $panne->setSuitesDonnes($test->getSuitesDonnes());
                    //$car->setPannes($panne);
                    $panne->setCars($car);*/


                    $car->setEtatCar($etat_car);
                    $car->setDescPanneCar($test->getDescPanne());
                    $car->setDateMaj(new \DateTime());
                    $car->setAuteur($test->getAuteur());
                    $car->setDateFinPanne($test->getDateEffective());

                    $date_prev = $test->getDatePrev();
                    $date_effective = $test->getDateEffective();



                    if( $date_effective != null  && $date_prev != null)
                    {
                        // Durée de panne
                        $duree_panne_prev = date_diff($date_prev, $date_effective);
                        $duree_panne_prev = $duree_panne_prev->format('%d');
                        $liste_panne->setDureePannePrev($duree_panne_prev);
                        $car->setDureePanne($duree_panne_prev);
                    }

                    /*$em->persist($car);*/



                    $em->flush();

                    return $this->redirectToRoute('car', array('id' => $id));

                }
            }
        }


        return $this->render('front/edit-panne.html.twig',
            array(
            'car_panne'             => $car,
            'liste_panne'           => $liste_panne,
            'liste_panne_anterieur' => $liste_panne_anterieur,
            'form'                  => $form->createView()
            ));
    }

    public function EditpanneAnt(Request $req, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $panne = $em->getRepository(Panne::class)->find($id);

         $car_id =$panne->getCars();
         $id_car = $car_id->getId();



        //Edition d'un autocar
        $form = $this->get('form.factory')->create(PanneType::class, $panne);


        if ($req->isMethod('POST') && $form->handleRequest($req)->isValid())
        {

            $res = $form->getData();
           // $panne->setCars()
            //var_dump($res->getEtatCar());
            //die();
            $panne->setEtatCar($res->getEtatCar());
            $panne->setDescPanne($res->getDescPanne());
            $panne->setSuitesDonnes($res->getSuitesDonnes());
            $panne->setnaturePanne($res->getNaturePanne());
            //var_dump($res->getDatePrev());
            //die();

            if ($res->getDatePrev() && $res->getDateEffective())
            {
                 $deb_panne = $res->getDatePrev();
                 $fin_panne = $res->getDateEffective();
                $duree_panne = date_diff($deb_panne, $fin_panne);

                $duree_panne = $duree_panne->format('%d');

                $panne->setDureePannePrev($duree_panne);
            }

            $em->flush();
            return $this->redirectToRoute('car',
                array(
                 'id' => $id_car
                ));
        }

        return $this->render('front/edit-panne-ant.html.twig',
            array(
              'panne'       => $panne,
              'form'        => $form->createView(),
            )
            );
    }

    public function deletePanne($id)
    {
        $em = $this->getDoctrine()->getManager();
        $panne = $em->getRepository(Panne::class)->findOneBy(array(
            'id'=> $id
        ));

        //recupere le car associe
        $car = $panne->getCars()->getId();


        $em->remove($panne);
        $em->flush();

        return $this->redirectToRoute('car', array('id' =>$car));


    }

    public function changeEtatCar($id)
    {
        $em = $this->getDoctrine()->getManager();
        $car = $em->getRepository(Cars::class)->findOneBy(array('id' => $id));

        $last_panne = $em->getRepository(Panne::class)->findOneBy(
            ['cars'  => $car ],
            ['id'   => 'DESC']
        );

        var_dump($last_panne->getId());
        die();

    }


}