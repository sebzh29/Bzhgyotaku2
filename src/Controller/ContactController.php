<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    /**
     * @Route("/nous-contacter", name="contact")
     */
    public function index(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('notice', 'Merci de nous avoir contacté. Notre équipe va vous répondre dans les meilleurs délais.');

            
            // On peut brancher une API SAV comme ZENDESK
            // En mail - adresse mail de contact - nom de la boutique - le sujet - le contenu
                        
            $mail = new Mail();
            $message = $form->get('content')->getData();
            $content = "Ci dessous le message reçu de la demande de contact.<br/>$message";
            
            $mail->send('glippa.sebastien@gmail.com', 'Breizh Gyotaku', 'Vous avez reçu une nouvelle demande de contact.', $content);
        }
        return $this->render('contact/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
