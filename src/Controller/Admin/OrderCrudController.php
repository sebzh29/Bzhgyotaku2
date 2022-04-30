<?php

namespace App\Controller\Admin;

use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class OrderCrudController extends AbstractCrudController
{
    private $entityManager;
    private $adminUrlGenerator;

    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $updatePreparation = Action::new('updatePreparation', 'Préparation en cours', 'fas fa-box-open')->linkToCrudAction('updatePreparation');
        $updateDelivery = Action::new('updateDelivery', 'Livraison en cours', 'fas fa-truck')->linkToCrudAction('updateDelivery');

        return $actions
            ->add('detail', $updateDelivery)
            ->add('detail', $updatePreparation)
            ->add('index', 'detail');
    }

    public function updatePreparation(AdminContext $context)
    {
        $order = $context->getEntity()->getInstance();
        // dd($order);
        $order->setState(2);
        $this->entityManager->flush();

        // notification écran verte
        $this->addFlash('notice', "<span style='color:green;'><strongVotre commande ".$order->getReference()." est bien <u>en cours de préparation</u>.</strong></span>");
        
        $url = $this->adminUrlGenerator
            ->setController(OrderCrudController::class)
            ->setAction('index') 
            ->generateUrl();

        // envoi d'un mail pour avertir notre utilisateur que la commande est en cours de préparation

        $mail = new Mail();        
        $content = "Bonjour ".$order->getUser()->getFirstname()."<br/>Votre commande ".$order->getReference()." est en cours de préparation.<br><br/>";
        $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Votre commande Breizh Gyotaku est en cours de préparation.', $content);  

            return $this->redirect($url);
  
    }

    public function updateDelivery(AdminContext $context)
    {
        $order = $context->getEntity()->getInstance();
        // dd($order);
        $order->setState(3);
        $this->entityManager->flush();

        //notification écran orange
        $this->addFlash('notice', "<span style='color:orange;'><strong>Votre commande ".$order->getReference()." est bien <u>en cours de livraison</u>.</strong></span>");
        
        $url = $this->adminUrlGenerator
            ->setController(OrderCrudController::class)
            ->setAction('index') 
            ->generateUrl();

        // envoi d'un mail pour avertir notre utilisateur que la commande est en cours de livraison

        $mail = new Mail();        
        $content = "Bonjour ".$order->getUser()->getFirstname()."<br/>Votre commande ".$order->getReference()." est en cours de livraison.<br><br/>";
        $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Votre commande Breizh Gyotaku est en cours de livraison.', $content);

            return $this->redirect($url);
  
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['id' => 'DESC'])
            ->showEntityActionsInlined();
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            DateTimeField::new('createdAt', 'Passée le'),
            TextField::new('user.getFullName', 'Utilisateur'),
            TextEditorField::new('delivery', 'Adresse de livraison')->formatValue(function ($value) { return $value; })->onlyOnDetail(),
            MoneyField::new('total', 'Total produit')->setCurrency('EUR'),
            TextField::new('carrierName', 'Transporteur'),
            MoneyField::new('carrierPrice', 'Frais de port')->setCurrency('EUR'),
            ChoiceField::new('state', 'statut')->setChoices([
                'Non payée' => 0,
                'Payée' => 1,
                'Préparation en cours' => 2,
                'Livraison en cours' => 3
            ]),
            ArrayField::new('orderDetails', 'Produits achetés')->hideOnIndex()
        ];
    }
    
}
