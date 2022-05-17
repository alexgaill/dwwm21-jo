<?php

namespace App\Controller;

use App\Entity\Pays;
use App\Form\PaysType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;

class PaysController extends AbstractController
{
    #[Route('/pays', name: 'app_pays', methods:['GET'])]
    public function index(ManagerRegistry $manager): Response
    {
        return $this->render('pays/index.html.twig', [
            'paysList' => $manager->getRepository(Pays::class)->findAll()
        ]);
    }

    #[Route("/pays/add", name:"app_add_pays", methods:['GET', 'POST'])]
    public function add(Request $request, ManagerRegistry $manager): Response
    {
        $pays = new Pays;
        $form = $this->createForm(PaysType::class, $pays);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $drapeau = $form->get('drapeau')->getData();
            if ($drapeau) {
                $drapeauName = md5(uniqid()). '.' . $drapeau->guessExtension();
                $drapeau->move($this->getParameter('drapeau_dir'), $drapeauName);
                $pays->setDrapeau($drapeauName);
                
                $manager->getRepository(Pays::class)->add($pays, true);
                $this->addFlash('success', 'Pays ajouté');
            } else {
                $this->addFlash('danger', 'Il manque le drapeau du pays');
            }
            return $this->redirectToRoute('app_pays');
        }
        return $this->renderForm('pays/add.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/pays/{id}/update', name:'app_update_pays', methods:['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function update (Pays $pays, Request $request, ManagerRegistry $manager): Response
    {
        if ($pays->getDrapeau()) {
            $oldDrapeau = $pays->getDrapeau();
            $pays->setDrapeau(
                new File($this->getParameter('drapeau_dir') .'/'. $pays->getDrapeau())
            );
        }
        $form = $this->createForm(PaysType::class, $pays);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $drapeau = $form->get('drapeau')->getData();
            if ($drapeau) {
                unlink($this->getParameter('drapeau_dir'). '/' .$oldDrapeau);
                $drapeauName = md5(uniqid()). '.' . $drapeau->guessExtension();
                $drapeau->move($this->getParameter('drapeau_dir'), $drapeauName);
                $pays->setDrapeau($drapeauName);
            } else {
                $pays->setDrapeau($oldDrapeau);
            }
            $manager->getRepository(Pays::class)->add($pays, true);
            $this->addFlash('success', 'Pays modifié');
            return $this->redirectToRoute('app_pays');
        }

        return $this->renderForm('pays/update.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/pays/{id}/delete', name:'app_delete_pays', methods:['GET'], requirements: ['id' => '\d+'])]
    public function delete(Pays $pays, ManagerRegistry $manager): Response
    {
        $manager->getRepository(Pays::class)->remove($pays, true);
        $this->addFlash('success', "Le pays ". $pays->getNom()." ne participe plus");
        return $this->redirectToRoute('app_pays');
    }
}
