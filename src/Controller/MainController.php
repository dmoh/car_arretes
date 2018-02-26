<?php
/**
 * Created by PhpStorm.
 * User: Service Info
 * Date: 27/12/2017
 * Time: 15:43
 */
namespace App\Controller;

use App\Entity\Rubrique;
use App\Form\RubriqueType;
use App\Form\UserType;
use App\Repository\PanneRepository;
use Doctrine\DBAL\Types\DateType;

use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use App\Form\CarType;
use App\Entity\Cars;
use App\Entity\Panne;
use App\Form\PanneType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Repository\CarsRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Cookie;

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



    public function consultation(Request $request, AuthenticationUtils $authUtils)
    {
        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT c FROM App\Entity\Cars c";
        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator-> paginate(
          $query,
          $request->query->getInt('page', 1),20
        );

        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository(Cars::class);
        $car_listing = $repository->findBy(array(),
             array('date' => 'DESC')
        );

        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();


        /*var_dump($car_listing);
        die();*/

        return $this->render('front/consultation.html.twig',
            //array('Cars' => $car_listing)
            array(
                'pagination'=> $pagination,
                'Cars' => $car_listing,
                'last_username' => $lastUsername,
                'error'         => $error,
            )
            );
    }


    //Historique du Car
    public function car(Request $req, $id)
    {
        $repo = $this->getDoctrine()->getManager();
        $kms = 0;
        $geoc_lat = 0;
        $geoc_long = 0;
        $car_info = $repo->getRepository(Cars::class)->find(
           $id
        );


        $immat =  $car_info->getImmat();
        $ti = time();

        if($immat !== "CS-223-JK" && $immat !== "BZ-668-TH" && $immat !== "BM-276-ZC" && $immat !== "BL-182-AH" && $immat)
        {
             $hache = base64_encode(hash_hmac("SHA1", "apa-aps-t39-c1ws.truckonline.proGET/apis/rest/v2.2/fleet/vehicles?vehicle_vrn=".$immat."".$ti."", "5a35101a-62ae-4cba-b70a-b1efd5cd75f0", true));
            $opts = array(
                'http' => array(
                    'method'=>'GET',
                    'header' => "x-tonl-client-id:  apa-aps-t39-c1\r\n".
                                "x-tonl-timestamp:  ".$ti."\r\n".
                                "x-tonl-signature: ".$hache.""
                )
            );
            // Recherche api truck online tous les vehicules
            $context = stream_context_create($opts);
            $kil = file_get_contents("https://ws.truckonline.pro/apis/rest/v2.2/fleet/vehicles?vehicle_vrn=".$immat."", false, $context);
            $result = json_decode($kil, true);

            if($result[0])
            {
                $kms = $result[0]["totalKms"];
            }

            $hache2 = base64_encode(hash_hmac("SHA1", "apa-aps-t39-c1ws.truckonline.proGET/apis/rest/v2.2/gpstracking?count=1&vehicle_vrn=".$immat."".$ti."", "5a35101a-62ae-4cba-b70a-b1efd5cd75f0", true));

            $opts2 = array(
                'http' => array(
                    'method'=>'GET',
                    'header' => "x-tonl-client-id:  apa-aps-t39-c1\r\n".
                        "x-tonl-timestamp:  ".$ti."\r\n".
                        "x-tonl-signature: ".$hache2.""
                )
            );
            $context2 = stream_context_create($opts2);
            $kil2 = file_get_contents("https://ws.truckonline.pro/apis/rest/v2.2/gpstracking?count=1&vehicle_vrn=".$immat."", false, $context2);
            $result2 = json_decode($kil2, true);
            if($result2[0])
            {
                $geoc_lat = $result2[0]["gpsInfo"]["latitude"];
                $geoc_long = $result2[0]["gpsInfo"]["longitude"];
            }

        }
        $em = $this->getDoctrine()->getManager();

        $pannes = $repo->getRepository(Panne::class)
                 ->findBy(array(
                     'cars' => $car_info),
                     array('id' => 'DESC',
                 ));
        $nombrep = count($pannes);

        if($nombrep == 0)
        {
            if($car_info->getEtatCar() != 'roulant' && $car_info->getDatePanneDeb() != null && $car_info->getDatePanneDeb() != '' ) {


                $car_info->setDatePanneFin(new \DateTime());
                $v = $car_info->getDatePannefin();
                $car_info->setEtatCar('roulant');
                /*$car_info->setDatePanneFin(new \DateTime());
                $fin_panne = $car_info->getDatePannefin();*/
                $s = $car_info->getDatePanneDeb();
                if (!$v || $v!= null && !$s || $s != null) {
                    # code...
                    $duree_panne = date_diff($s, $v);
                    $duree_panne_prev = $duree_panne->format('%d');
                    $car_info->setDureePanne($duree_panne_prev);
                }

                $em->flush();


            }
        }

        $car_info->setCompteurPanne($nombrep);

        return $this->render('front/car.html.twig',
            array(
                'car'       => $car_info,
                'nombrep'   => $nombrep,
                'pannes'    => $pannes,
                'kms'       => $kms,
                'geoc_lat'  => $geoc_lat,
                'geoc_long' => $geoc_long,
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
        $condition_garantie = $car->getConditionGarantie();



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
                    $panne->setGarantie($test->getGarantie());

                    $car->setAuteur($test->getAuteur());
                    $car->setNaturePanneCar($test->getNaturePanne());
                    $car->setDescPanneAno($test->getDescPanne());
                    $car->setDescPanneCar($test->getDescPanne());
                    $car->setGarantie($test->getGarantie());

                }
                elseif ($etat_car == 'atelier')
                {
                    $panne->setCars($car);
                    $d_today = new \DateTime();
                    $d_prev = $test->getDatePrev();
                    $panne->setNaturePanne($test->getNaturePanne());
                    $panne->setAuteur($test->getAuteur());
                    $panne->setGarantie($test->getGarantie());

                    $date_prev = $test->getDatePrev();
                    $date_effective = $test->getDateEffective();

                    if($date_effective != null  && $date_prev != null)
                    {
                        // Durée de panne
                        $duree_panne_prev = date_diff($date_prev, $date_effective);
                        $duree_panne_prev = $duree_panne_prev->format('%d');

                        $panne->setDureePannePrev($duree_panne_prev);
                        $car->setDureePanne($duree_panne_prev);

                    }

                    if($d_today >= $d_prev)
                    {}
                        $car->setGarantie($test->getGarantie());
                        $car->setAuteur($test->getAuteur());
                        $car->setDateMaj(new \DateTime());
                        $car->setNaturePanneCar($test->getNaturePanne());
                        $car->setDescPanneCar($test->getDescPanne());
                        $car->setEtatCar($test->getEtatCar());


                    $em -> persist($car);
                    $em -> persist($panne);
                    $em -> flush();

                    return $this->redirectToRoute('liste_panne');

                }
                else if ($etat_car == 'panne')
                {
                    $panne->setCars($car);
                    $d_today = new \DateTime();
                    $d_prev = $test->getDatePrev();
                    $panne->setNaturePanne($test->getNaturePanne());
                    $panne->setAuteur($test->getAuteur());
                    $panne->setGarantie($test->getGarantie());

                    $date_prev = $test->getDatePrev();
                    $date_effective = $test->getDateEffective();

                    if($date_effective != null  && $date_prev != null)
                    {
                        // Durée de panne
                        $duree_panne_prev = date_diff($date_prev, $date_effective);
                        $duree_panne_prev = $duree_panne_prev->format('%d');

                        $panne->setDureePannePrev($duree_panne_prev);
                        $car->setDureePanne($duree_panne_prev);

                    }

                    if($d_today >= $d_prev)
                    {}
                    $car->setGarantie($test->getGarantie());
                    $car->setAuteur($test->getAuteur());
                    $car->setDateMaj(new \DateTime());
                    $car->setNaturePanneCar($test->getNaturePanne());
                    $car->setDescPanneCar($test->getDescPanne());
                    $car->setEtatCar($test->getEtatCar());


                    $em -> persist($car);
                    $em -> persist($panne);
                    $em -> flush();

                    $panne->setCars($car);
                    $d_today = new \DateTime();
                    $d_prev = $test->getDatePrev();
                    $panne->setNaturePanne($test->getNaturePanne());
                    $panne->setAuteur($test->getAuteur());
                    $panne->setGarantie($test->getGarantie());

                    $date_prev = $test->getDatePrev();
                    $date_effective = $test->getDateEffective();

                    if($date_effective != null  && $date_prev != null)
                    {
                        // Durée de panne
                        $duree_panne_prev = date_diff($date_prev, $date_effective);
                        $duree_panne_prev = $duree_panne_prev->format('%d');

                        $panne->setDureePannePrev($duree_panne_prev);
                        $car->setDureePanne($duree_panne_prev);

                    }

                    if($d_today >= $d_prev)
                    {}
                    $car->setGarantie($test->getGarantie());
                    $car->setAuteur($test->getAuteur());
                    $car->setDateMaj(new \DateTime());
                    $car->setNaturePanneCar($test->getNaturePanne());
                    $car->setDescPanneCar($test->getDescPanne());
                    $car->setEtatCar($test->getEtatCar());


                    $em -> persist($car);
                    $em -> persist($panne);
                    $em -> flush();

                    return $this->redirectToRoute('liste_panne');
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
                'cond_garantie'=> $condition_garantie,
                )
            );

    }



    public function recherche(Request $req)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();
        $car_trouver = 'rien';
        $form = $this->createFormBuilder()
                ->add('recherche', TextType::class, array(
                    'required' => false
                ))
                ->add('Rechercher', SubmitType::class)
                ->getForm();

        $repository = $this->getDoctrine()->getManager()->getRepository(Cars::class);

        $car_listing = $repository->findBy(array(), array('date' => 'DESC'));


        $form_avancee = $this->createFormBuilder()
            ->add('date', \Symfony\Component\Form\Extension\Core\Type\DateType::class, array(
                'widget' => 'single_text',
                'required'      =>  false,
                'html5'         =>  false,
                'format'        =>  'd/MM/yyyy'
            ))
            ->add('date_2', \Symfony\Component\Form\Extension\Core\Type\DateType::class, array(
                'widget' => 'single_text',
                'required'  =>  false,
                'format'    =>  'd/MM/yyyy'
            ))
            ->add('recherche_av', TextType::class, array(
                'required' => false
            ))
            ->add('marque', ChoiceType::class, array(
                'choices' => array(
                    'Sélectionner une marque'   => null,
                    'IVECO CROSSWAY'             => "iveco crossway",
                    'IVECO MAGELYS'              => "iveco magelys",
                    'MERCEDES TOURISMO'          => 'mercedes tourismo',
                    'IVECO DAILY'                => "iveco daily",
                    'VOLVO 9700HD'               => "volvo 9700 hd",
                    'SCANIA TOURING'             => "scania touring",
                    'IVECO RECREO'               => "iveco recreo",
                    'SOLARIS'                    => "solaris",
                    'BOVA'                       => "bova",
                    'IRIZAR I4'                  => "irizar i4",
                ),))
            ->add('Valider', SubmitType::class)
            ->getForm();
        //ajax 
        if ($req->isXmlHttpRequest()) { 
        }

         if( $req->isMethod('POST') )
        {
            $form->handleRequest($req);

            $immat_car = $form->getData();
            if($immat_car['recherche'] == NULL )
            {
                $form_avancee->handleRequest($req);
                $form_av = $form_avancee->getData();

                if($form_av['date'] && $form_av['date_2'])
                {
                    $date_de = $form_av['date'];
                    $date_a = $form_av['date_2'];

                    if($form_av['recherche_av'] && $form_av['marque'] === null || $form_av['marque'] == null || $form_av['marque'] == '' )
                    {
                        $mot_cles = $form_av['recherche_av'];
                        $query = $em->createQuery('SELECT p FROM App\Entity\Panne p WHERE p.nature_panne LIKE :nature')
                                    ->setParameter( 'nature','%'.$mot_cles.'%');

                        $car_trouver = $query->getResult();
                        $r = count($car_trouver);


                        $form = $this->createFormBuilder()
                            ->add('recherche', TextType::class)
                            ->add('Rechercher', SubmitType::class)
                            ->getForm();

                        $form_avancee = $this->createFormBuilder()
                            ->add('date', \Symfony\Component\Form\Extension\Core\Type\DateType::class, array(
                                'widget' => 'single_text',
                                'required'      =>  false,
                                'html5'         =>  false,
                                'format'        =>  'd/MM/yyyy'
                            ))
                            ->add('date_2', \Symfony\Component\Form\Extension\Core\Type\DateType::class, array(
                                'widget' => 'single_text',
                                'required'  =>  false,
                                'format'    =>  'd/MM/yyyy'
                            ))
                            ->add('recherche_av', TextType::class, array(
                                'required' => false
                            ))
                            ->add('marque', ChoiceType::class, array(
                                'choices' => array(
                                    'Sélectionner une marque'   => null,
                                    'IVECO CROSSWAY'             => "iveco crossway",
                                    'IVECO MAGELYS'              => "iveco magelys",
                                    'MERCEDES TOURISMO'          => 'mercedes tourismo',
                                    'IVECO DAILY'                => "iveco daily",
                                    'VOLVO 9700HD'               => "volvo 9700 hd",
                                    'SCANIA TOURING'             => "scania touring",
                                    'IVECO RECREO'               => "iveco recreo",
                                    'SOLARIS'                    => "solaris",
                                    'BOVA'                       => "bova",
                                    'IRIZAR I4'                  => "irizar i4",
                                ),))
                            ->add('Valider', SubmitType::class)
                            ->getForm();;



                        return $this->render('front/recherche.html.twig', array(
                            'form' => $form->createView(),
                            'form_avancee' => $form_avancee->createView(),
                            'car' => 'trouver',
                            'car_panne_listing'  => $car_trouver,
                            'plusieurs' => 'OK',
                        ));

                    }
                    elseif ($form_av['recherche_av'] && $form_av['marque'] != null)
                    {
                        $marque = $form_av['marque'];
                        $mot_cles = $form_av['recherche_av'];
                        //*$query = $em->createQuery('SELECT p FROM App\Entity\Panne p WHERE p.nature_panne LIKE :nature')
                        // ->setParameter( 'nature','%'.$marque.'%');


                        $query = $em->createQuery('SELECT p FROM App\Entity\Panne p JOIN App\Entity\Cars car WHERE car.marque = :marque AND p.nature_panne LIKE :mots AND p.date_prev BETWEEN :date1 AND :date2 ')
                            ->setParameter('marque', $marque)
                            ->setParameter('mots',   '%'.$mot_cles.'%')
                            ->setParameter('date1', $date_de)
                            ->setParameter('date2', $date_a);

                        $car_trouver = $query->getResult();
                        $r = count($car_trouver);

                        for($i = 0 ; $i < $r ; $i++)
                        {
                            $marque_recu = $car_trouver[$i]->getCars()->getMarque();
                            if($marque != $marque_recu)
                            {
                                unset($car_trouver[$i]);
                            }

                        }


                        // $r2 = count($car_trouver);


                        $form = $this->createFormBuilder()
                            ->add('recherche', TextType::class)
                            ->add('Rechercher', SubmitType::class)
                            ->getForm();

                        $form_avancee = $this->createFormBuilder()
                            ->add('date', \Symfony\Component\Form\Extension\Core\Type\DateType::class, array(
                                'widget' => 'single_text',
                                'required'      =>  false,
                                'html5'         =>  false,
                                'format'        =>  'd/MM/yyyy'
                            ))
                            ->add('date_2', \Symfony\Component\Form\Extension\Core\Type\DateType::class, array(
                                'widget' => 'single_text',
                                'required'  =>  false,
                                'format'    =>  'd/MM/yyyy'
                            ))
                            ->add('recherche_av', TextType::class, array(
                                'required' => false
                            ))
                            ->add('marque', ChoiceType::class, array(
                                'choices' => array(
                                    'Sélectionner une marque'   => null,
                                    'IVECO CROSSWAY'             => "iveco crossway",
                                    'IVECO MAGELYS'              => "iveco magelys",
                                    'MERCEDES TOURISMO'          => 'mercedes tourismo',
                                    'IVECO DAILY'                => "iveco daily",
                                    'VOLVO 9700HD'               => "volvo 9700 hd",
                                    'SCANIA TOURING'             => "scania touring",
                                    'IVECO RECREO'               => "iveco recreo",
                                    'SOLARIS'                    => "solaris",
                                    'BOVA'                       => "bova",
                                    'IRIZAR I4'                  => "irizar i4",
                                ),))
                            ->add('Valider', SubmitType::class)
                            ->getForm();;



                        return $this->render('front/recherche.html.twig', array(
                            'form' => $form->createView(),
                            'form_avancee' => $form_avancee->createView(),
                            'car' => 'trouver',
                            'car_panne_listing'  => $car_trouver,
                            'plusieurs' => 'OK',
                        ));
                    }
                    elseif ( $form_av['recherche_av'] === '' || $form_av['recherche_av'] === null && $form_av['marque'] !== null || $form_av['marque'] != null || $form_av['marque'] != '')
                    {
                        $marque = $form_av['marque'];



                        //*$query = $em->createQuery('SELECT p FROM App\Entity\Panne p WHERE p.nature_panne LIKE :nature')
                                   // ->setParameter( 'nature','%'.$marque.'%');

                        $query = $em->createQuery('SELECT p FROM App\Entity\Panne p JOIN App\Entity\Cars car WITH car.marque = :marque WHERE p.date_prev BETWEEN :date1 AND :date2 ')
                                    ->setParameter('marque', $marque)
                                    ->setParameter('date1', $date_de)
                                    ->setParameter('date2', $date_a);

                        $car_trouver = $query->getResult();

                        $r = count($car_trouver);



                        for($i = 0 ; $i < $r ; $i++)
                        {
                             $marque_recu = $car_trouver[$i]->getCars()->getMarque();
                            if($marque != $marque_recu)
                            {
                                unset($car_trouver[$i]);
                            }

                        }




                        $form = $this->createFormBuilder()
                            ->add('recherche', TextType::class)
                            ->add('Rechercher', SubmitType::class)
                            ->getForm();

                        $form_avancee = $this->createFormBuilder()
                            ->add('date', \Symfony\Component\Form\Extension\Core\Type\DateType::class, array(
                                'widget' => 'single_text',
                                'required'      =>  false,
                                'html5'         =>  false,
                                'format'        =>  'd/MM/yyyy'
                            ))
                            ->add('date_2', \Symfony\Component\Form\Extension\Core\Type\DateType::class, array(
                                'widget' => 'single_text',
                                'required'  =>  false,
                                'format'    =>  'd/MM/yyyy'
                            ))
                            ->add('recherche_av', TextType::class, array(
                                'required' => false
                            ))
                            ->add('marque', ChoiceType::class, array(
                                'choices' => array(
                                    'Sélectionner une marque'   => null,
                                    'IVECO CROSSWAY'             => "iveco crossway",
                                    'IVECO MAGELYS'              => "iveco magelys",
                                    'MERCEDES TOURISMO'          => 'mercedes tourismo',
                                    'IVECO DAILY'                => "iveco daily",
                                    'VOLVO 9700HD'               => "volvo 9700 hd",
                                    'SCANIA TOURING'             => "scania touring",
                                    'IVECO RECREO'               => "iveco recreo",
                                    'SOLARIS'                    => "solaris",
                                    'BOVA'                       => "bova",
                                    'IRIZAR I4'                  => "irizar i4",
                                ),))
                            ->add('Valider', SubmitType::class)
                            ->getForm();;



                        return $this->render('front/recherche.html.twig', array(
                            'form' => $form->createView(),
                            'form_avancee' => $form_avancee->createView(),
                            'car' => 'trouver',
                            'car_panne_listing'  => $car_trouver,
                            'plusieurs' => 'OK',
                        ));
                    }
                    else
                        {
                        $date = $form_av['date'];
                        $date2 = $form_av['date_2'];

                        $query = $em->createQuery('SELECT p FROM App\Entity\Panne p WHERE p.date_prev BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date)
                            ->setParameter('date2', $date2);

                            $car_trouver = $query->getResult();
                            $r = count($car_trouver);


                            $form = $this->createFormBuilder()
                                ->add('recherche', TextType::class)
                                ->add('Rechercher', SubmitType::class)
                                ->getForm();

                            $form_avancee = $this->createFormBuilder()
                                ->add('date', \Symfony\Component\Form\Extension\Core\Type\DateType::class, array(
                                    'widget' => 'single_text',
                                    'required'      =>  false,
                                    'html5'         =>  false,
                                    'format'        =>  'd/MM/yyyy'
                                ))
                                ->add('date_2', \Symfony\Component\Form\Extension\Core\Type\DateType::class, array(
                                    'widget' => 'single_text',
                                    'required'  =>  false,
                                    'format'    =>  'd/MM/yyyy'
                                ))
                                ->add('recherche_av', TextType::class, array(
                                    'required' => false
                                ))
                                ->add('marque', ChoiceType::class, array(
                                    'choices' => array(
                                        'Sélectionner une marque'   => null,
                                        'IVECO CROSSWAY'             => "iveco crossway",
                                        'IVECO MAGELYS'              => "iveco magelys",
                                        'MERCEDES TOURISMO'          => 'mercedes tourismo',
                                        'IVECO DAILY'                => "iveco daily",
                                        'VOLVO 9700HD'               => "volvo 9700 hd",
                                        'SCANIA TOURING'             => "scania touring",
                                        'IVECO RECREO'               => "iveco recreo",
                                        'SOLARIS'                    => "solaris",
                                        'BOVA'                       => "bova",
                                        'IRIZAR I4'                  => "irizar i4",
                                    ),))
                                ->add('Valider', SubmitType::class)
                                ->getForm();;



                            return $this->render('front/recherche.html.twig', array(
                                'form' => $form->createView(),
                                'form_avancee' => $form_avancee->createView(),
                                'car' => 'trouver',
                                'car_panne_listing'  => $car_trouver,
                                'plusieurs' => 'OK',
                            ));
                    }

                }// endif $form_av['date'] && $form_av['date_2']
                elseif($form_av['recherche_av'] && $form_av['marque'])
                {
                    $mot_cles = $form_av['recherche_av'];
                    $marque = $form_av['marque'];

                    $query = $em->createQuery('SELECT p FROM App\Entity\Panne p JOIN App\Entity\Cars car WHERE car.marque = :marque AND p.nature_panne LIKE :mots ')
                        ->setParameter('marque', $marque)
                        ->setParameter('mots',   '%'.$mot_cles.'%');

                    $car_trouver = $query->getResult();
                    $r = count($car_trouver);


                    $form = $this->createFormBuilder()
                        ->add('recherche', TextType::class)
                        ->add('Rechercher', SubmitType::class)
                        ->getForm();

                    $form_avancee = $this->createFormBuilder()
                        ->add('date', \Symfony\Component\Form\Extension\Core\Type\DateType::class, array(
                            'widget' => 'single_text',
                            'required'      =>  false,
                            'html5'         =>  false,
                            'format'        =>  'd/MM/yyyy'
                        ))
                        ->add('date_2', \Symfony\Component\Form\Extension\Core\Type\DateType::class, array(
                            'widget' => 'single_text',
                            'required'  =>  false,
                            'format'    =>  'd/MM/yyyy'
                        ))
                        ->add('recherche_av', TextType::class, array(
                            'required' => false
                        ))
                        ->add('marque', ChoiceType::class, array(
                            'choices' => array(
                                'Sélectionner une marque'   => null,
                                'IVECO CROSSWAY'             => "iveco crossway",
                                'IVECO MAGELYS'              => "iveco magelys",
                                'MERCEDES TOURISMO'          => 'mercedes tourismo',
                                'IVECO DAILY'                => "iveco daily",
                                'VOLVO 9700HD'               => "volvo 9700 hd",
                                'SCANIA TOURING'             => "scania touring",
                                'IVECO RECREO'               => "iveco recreo",
                                'SOLARIS'                    => "solaris",
                                'BOVA'                       => "bova",
                                'IRIZAR I4'                  => "irizar i4",
                            ),))
                        ->add('Valider', SubmitType::class)
                        ->getForm();;



                    return $this->render('front/recherche.html.twig', array(
                        'form' => $form->createView(),
                        'form_avancee' => $form_avancee->createView(),
                        'car' => 'trouver',
                        'car_panne_listing'  => $car_trouver,
                        'plusieurs' => 'OK',
                    ));
                }
            }

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
                  ->setMaxResults(10)
                  ->getQuery()
                  ->getResult();

                $form = $this->createFormBuilder()
                        ->add('recherche', TextType::class)
                        ->add('Rechercher', SubmitType::class)
                        ->getForm();

               $form_avancee = $this->createFormBuilder()
                   ->add('date', \Symfony\Component\Form\Extension\Core\Type\DateType::class, array(
                       'widget' => 'single_text',
                       'required'      =>  false,
                       'html5'         =>  false,
                       'format'        =>  'd/MM/yyyy'
                   ))
                   ->add('date_2', \Symfony\Component\Form\Extension\Core\Type\DateType::class, array(
                       'widget' => 'single_text',
                       'required'  =>  false,
                       'format'    =>  'd/MM/yyyy'
                   ))
                   ->add('recherche_av', TextType::class, array(
                       'required' => false
                   ))
                   ->add('marque', ChoiceType::class, array(
                       'choices' => array(
                           'Sélectionner une marque'   => null,
                           'IVECO CROSSWAY'             => "iveco crossway",
                           'IVECO MAGELYS'              => "iveco magelys",
                           'MERCEDES TOURISMO'          => 'mercedes tourismo',
                           'IVECO DAILY'                => "iveco daily",
                           'VOLVO 9700HD'               => "volvo 9700 hd",
                           'SCANIA TOURING'             => "scania touring",
                           'IVECO RECREO'               => "iveco recreo",
                           'SOLARIS'                    => "solaris",
                           'BOVA'                       => "bova",
                           'IRIZAR I4'                  => "irizar i4",
                       ),))
                   ->add('Valider', SubmitType::class)
                   ->getForm();


                if(!$car_trouver)
                {
                    $car_trouver = $qb->select('a')->from('App\Entity\Cars', 'a')
                        ->where($qb->expr()->like('a.marque', $qb->expr()->literal('%'.$imm.'%')))
                        ->setMaxResults(20)
                        ->getQuery()
                        ->getResult();
                }

                return $this->render('front/recherche.html.twig', array(
                    'form' => $form->createView(),
                    'form_avancee' => $form_avancee->createView(),
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
            'form_avancee' => $form_avancee->createView(),
            'car'  => $car_trouver,
            'car_listing' => $car_listing,
            'plusieurs' => 'debut',
        ));
    }

    public function listePanne(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /*$car_en_atelier = $em->getRepository(Panne::class)->findBy(
          'eta'
        );*/

        $date_atelier = $em->getRepository(Panne::class)->findParDateAtelier("atelier");
        /*var_dump($s);
        die();*/

        $list_panne = $em->getRepository(Cars::class)->findBy(
          ['etat_car'  => 'panne' ],
          ['id'       => 'DESC']
        );

        $list_atelier = $em->getRepository(Cars::class)->findBy(
           ['etat_car'  => 'atelier'],
           ['id'      => 'DESC']
        );/**/





        /* Vérifier la date
        $s = count($date_atelier);
        if($s > 0)
        {
            for($i=0 ; $i < $s ; $i++)
            {
                if($date_atelier[$i]->getCars()->getEtatCar() != "atelier")
                {
                    $date_atelier[$i]->getCars()->setEtatCar("atelier");
                    $nature=$date_atelier[$i]->getNaturePanne();
                    $desc=$date_atelier[$i]->getDescPanne();
                    $date_atelier[$i]->getCars()->setDateMaj(new \DateTime());
                    $date_atelier[$i]->getCars()->setNaturePanneCar($nature);
                    $date_atelier[$i]->getCars()->setDescPanneCar($desc);
                    $date_atelier[$i]->getCars()->setGarantie($date_atelier[$i]->getGarantie());
                    $date_atelier[$i]->getCars()->setDatePanneDeb($date_atelier[$i]->getDatePrev());
                }
            }
        }*/

        $list_panne_ano = $em->getRepository(Cars::class)->findBy(
          ['etat_car'   =>  'roulant_ano'],
          ['id'   =>  'DESC']
        );





        /*$list_panne_ano_p = $em->getRepository(Panne::class)->findBy(
           ['etat_car'  => 'roulant_ano'],
           ['id'        => 'DESC']
        );*/

        if(!$list_panne)
        {
            $list_panne = array();
        }

        if(!$list_panne_ano)
        {
            $list_panne_ano = array();
        }

        if(!$list_atelier)
        {
            $list_atelier = array();
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
            'liste_panne'       =>      $list_panne,
            'liste_panne_ano'   =>      $list_panne_ano,
            'liste_atelier'     =>      $list_atelier,
            'form'              =>      $form->createView(),
        ));
    }


    public function editCar(Request $request, $id){
        $em = $this->getDoctrine()->getManager();

        $car = $em->getRepository(Cars::class)->find($id);
        $panne = new Panne($car);


        $etat_car  = strtolower($car->getEtatCar());


        //Edition d'un autocar
        $form = $this->get('form.factory')->create(CarType::class, $car);


        /*$form->get('etat_car')->setData($etat_car);
        $dd = $form->getData();
        var_dump($dd->getEtatCar());
        die();*/

        if(null === $form)
        {
            throw new NotFoundHttpException("Cet autocar n'existe pas.");
        }

        if($request->isMethod('POST') && $form->handleRequest($request)->isValid())
        {
            $rep = $form->getData();
            
            $car->setAuteur($rep->getAuteur());
            //$rep->setDate(new \DateTime());
            $car->setEtatCar($etat_car);

            $em->flush();

            return $this->redirectToRoute('car', array('id'=> $car->getId()));

        }
        /*var_dump($form);
        die();*/
        return $this->render('front/editcar.html.twig',
                array('car'         => $car,
                      'etat_car'    => $etat_car,
                      'form'        => $form->createView(),
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
        $car_id = $car->getId();
        $c_garantie = $car->getConditionGarantie();


        $panne = new Panne($car);

        //$list= $em->getRepository(Panne::class)->getCarWithLastPanne($car->getId());
        $liste_panne = $em->getRepository(Panne::class)->findOneBy(
            ['cars'     => $car ],
            ['id'       => 'DESC']
        );

        $etat_panne = $liste_panne->getEtatCar();

        $liste_panne_anterieur = $em->getRepository(Panne::class)->findBy(
          ['cars'   =>  $car],
          ['id'   => 'DESC']
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

                if($etat_car == 'panne')
                {
                    $car->setEtatCar($etat_car);
                    $car->setDateMaj(new \DateTime());
                    $car->setAuteur($test->getAuteur());
                    $car->setGarantie($test->getGarantie());

                    $panne->setEtatCar($etat_car);
                    $panne->setAuteur($test->getAuteur());
                    $panne->setDatePrev($test->getDatePrev());
                    $panne->setDescPanne($test->getDescPanne());
                    $panne->setSuitesDonnes($test->getSuitesDonnes());
                    $panne->setGarantie($test->getGarantie());

                    $car->setNaturePanneCar($test->getNaturePanne());
                    $car->setDatePanneDeb($test->getDatePrev());
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

                    return $this->redirectToRoute('liste_panne');



                }
                elseif ($etat_car == 'roulant ano' || $etat_car  == 'roulant_ano' && $etat_actu == 'roulant_ano')
                {
                    //Roulant avec anomalie écrase panne

                    $car->setEtatCar($etat_actu);
                    $car->setDateMaj(new \DateTime());
                    $car->setGarantie($test->getGarantie());



                    $panne->setEtatCar($etat_car);
                    $panne->setAuteur($test->getAuteur());
                    $panne->setDatePrev($test->getDatePrev());
                    $panne->setGarantie($test->getGarantie());

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


                    return $this->redirectToRoute('liste_panne');
                }
                elseif ($etat_car == 'roulant ano' || $etat_car  == 'roulant_ano' && $etat_actu != 'roulant_ano')
                {
                    //Roulant avec anomalie écrase panne

                    $car->setEtatCar($etat_car);
                    $car->setDateMaj(new \DateTime());
                    $car->setGarantie($test->getGarantie());



                    $panne->setEtatCar($etat_car);
                    $panne->setAuteur($test->getAuteur());
                    $panne->setDatePrev($test->getDatePrev());
                    $panne->setGarantie($test->getGarantie());

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


                    return $this->redirectToRoute('liste_panne');
                }
                elseif ($etat_car == 'panne' && $etat_actu == 'panne')
                {
                    $panne->setCars($car);
                    $p = $em->getRepository(Panne::class)->find($test->getId());
                    $car->setNaturePanneCar($test->getNaturePanne());
                    $car->setGarantie($test->getGarantie());
                    $panne->setGarantie($test->getGarantie());

                    $em->persist($p);
                    $em->flush();

                   return $this->redirectToRoute('consultation');
                    //modifie la panne en cour

                }
                elseif ($etat_car == 'atelier')
                {
                    $car->setEtatCar($etat_car);
                    $car->setDateMaj(new \DateTime());
                    $car->setAuteur($test->getAuteur());
                    $car->setGarantie($test->getGarantie());

                    $panne->setEtatCar($etat_car);
                    $panne->setAuteur($test->getAuteur());
                    $panne->setDatePrev($test->getDatePrev());
                    $panne->setDescPanne($test->getDescPanne());
                    $panne->setSuitesDonnes($test->getSuitesDonnes());
                    $panne->setGarantie($test->getGarantie());

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

                    return $this->redirectToRoute('liste_panne');
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
            'c_garantie'            => $c_garantie,
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

    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('consultation');
        }
        return $this->render(
          'front/register.html.twig',
          array('form'=> $form->createView())
        );
    }

    public function login(Request $request, AuthenticationUtils $authUtils)
    {
        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('front/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }


    public function logout()
    {
        $this->get('security.token_storage')->setToken(NULL);
        $response = new Response();
        //$response->headers->setCookie(new Cookie('REMEMBERME', 'true', 0, '/', null, false, false));
        $response->headers->clearCookie('REMEMBERME');

        $response->send();
        return $this->redirectToRoute('consultation');
    }


    public function addRubrique(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $rub_rec = $em->getRepository(Rubrique::class)->findAll();
        $rub = new Rubrique();

        $form = $this->createForm(RubriqueType::class, $rub);


        if ($request->isMethod('POST'))
        {
                $form->handleRequest($request);
                $em->persist($rub);
                $em->flush();
                return $this->redirectToRoute('add_rubrique');
        }

        return $this->render('front/rubrique.html.twig', array(
                'form'  => $form->createView(),
                'rub'   => $rub_rec,
            ));


    }

    public function editRubrique(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $rub = $em->getRepository(Rubrique::class)->find($id);

        $form = $this->get('form.factory')->create(RubriqueType::class, $rub);

        if ($request->isMethod('POST'))
        {
            $form->handleRequest($request);
            $em->flush();
            return $this->redirectToRoute('add_rubrique');

        }

        return $this->render('front/edit-rubrique.html.twig',array(
           'form'   => $form->createView()
        ));
    }

    public function deleteRubrique(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $rub = $em->getRepository(Rubrique::class)->find($id);
        $em->remove($rub);
        $em->flush();
        return $this->redirectToRoute('add_rubrique');

    }
}