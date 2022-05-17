<?php

namespace App\Controller;

use App\Entity\Discipline;
use App\Form\DisciplineType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DisciplineController extends AbstractController
{
    #[Route('/discipline', name: 'app_discipline', methods:['GET', 'POST'])]
    public function index(ManagerRegistry $manager, Request $request): Response
    {
        $discipline = new Discipline;
        $form = $this->createForm(DisciplineType::class, $discipline);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->getRepository(Discipline::class)->add($discipline, true);
            $this->addFlash('success', "Discipline ajoutée");
            return $this->redirectToRoute('app_discipline');
        }

        return $this->renderForm('discipline/index.html.twig', [
            'disciplines'=> $manager->getRepository(Discipline::class)->findAll(),
            'form' => $form
        ]);
    }

    #[Route('/discipline/{id}/update', name:'app_update_discipline', methods:['GET', 'POST'], requirements:['id' => '\d+'])]
    public function update(Discipline $discipline, Request $request, ManagerRegistry $manager): Response
    {
        $form = $this->createForm(DisciplineType::class, $discipline);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->getRepository(Discipline::class)->add($discipline, true);
            $this->addFlash('success', 'Discipline modifiée');
            return $this->redirectToRoute('app_discipline');
        }

        return $this->renderForm('discipline/update.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/discipline/{id}/delete', name:'app_delete_discipline', methods:['GET'], requirements:['id' => '\d+'])]
    public function delete(Discipline $discipline, ManagerRegistry $manager): Response
    {
        $manager->getRepository(Discipline::class)->remove($discipline, true);
        $this->addFlash('success', "Discipline supprimée");
        return $this->redirectToRoute('app_discipline');
    }
}
