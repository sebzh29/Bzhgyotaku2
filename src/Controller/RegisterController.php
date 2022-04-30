<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/inscription", name="register")
     */
    public function index(Request $request, UserPasswordHasherInterface $hasher): Response
    {

        $notification = null;

        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);// notre form est capable d ecouter la requete

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();
            // dd($user);

            // Vérifier si mon utilisateur n'est pas déjà existant en base de données
            $search_email = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);

            if (!$search_email) {
                // Encodage mot de passe
                $password = $hasher->hashPassword($user,$user->getPassword());
                // dd($password);

                $user->setPassword($password);


                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $mail = new Mail();
                $content = "Bonjour ".$user->getFirstname()."<br/>Bienvenue sur la première boutique dédiée à l'univers du Gyotaku.<br><br/>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer venenatis id lectus ac molestie. Maecenas condimentum, erat auctor pharetra consectetur, lorem lacus fermentum orci, et tristique tellus odio et mi. Nunc molestie erat sed erat rhoncus, id suscipit eros vulputate.";
                $mail->send($user->getEmail(), $user->getFirstname(), 'Bienvenue sur Breizh Gyotaku', $content);

                $notification = "Votre inscription s'est correctement déroulée. Vous pouvez dès à présent vous connecter à votre compte.";

            } else {

                $notification = "L'email que vous avez renseigné existe déjà.";
            }
        }

        return $this->render('register/index.html.twig',[
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }
}
