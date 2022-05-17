<?php

namespace App\Controller;

use App\Entity\Athlete;
use App\Form\AthleteType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AthleteController extends AbstractController
{
    #[Route('/', name: 'app_athlete', methods:['GET', 'POST'])]
    public function index(Request $request, ManagerRegistry $manager): Response
    {
        $athlete = new Athlete;
        $form = $this->createForm(AthleteType::class, $athlete);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $profil = $form->get('photo')->getData();
            if ($profil) {
                $profilName = md5(uniqid()).'.'. $profil->guessExtension();
                $profil->move($this->getParameter('profil_dir'), $profilName);
                $athlete->setPhoto($profilName);

                $manager->getRepository(Athlete::class)->add($athlete, true);
                $this->addFlash('success', $athlete->getNom() . ' est enregistré');
                return $this->redirectToRoute('app_athlete');

            } else {
                $this->addFlash('danger', 'Photo de profil obligatoire');
            }
        }
        return $this->renderForm('athlete/index.html.twig', [
            'form' => $form,
            'athletes' => $manager->getRepository(Athlete::class)->findAll()
        ]);
    }

    #[Route('athlete/{id}/update', name:'app_update_athlete', methods:['GET', 'POST'], requirements:['id' => '\d+'])]
    public function update (Athlete $athlete, Request $request, ManagerRegistry $manager): Response
    {
        if ($athlete->getPhoto()) {
            $oldPhoto = $athlete->getPhoto();
            $athlete->setPhoto(
                new File($this->getParameter('profil_dir').'/'. $oldPhoto)
            );
        }

        $form = $this->createForm(AthleteType::class, $athlete);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photo = $form->get('photo')->getData();
            if ($photo) {
                unlink($this->getParameter('photo_dir').'/'. $oldPhoto);
                $photoName = md5(uniqid()).'.'. $photo->guessExtension();
                $photo->move($this->getParameter('profil_dir'), $photoName);
                $athlete->setPhoto($photoName);
            } else {
                $athlete->setPhoto($oldPhoto);
            }

            $manager->getRepository(Athlete::class)->add($athlete, true);
            $this->addFlash('success', "Les informations de l'atlhète sont modifiées");
            return $this->redirectToRoute('app_athlete');
        }

        return $this->renderForm('athlete/update.html.twig',[
            'form' => $form
        ]);
    }

    #[Route('athlete/{id}/delete', name:'app_delete_athlete', methods:['GET'], requirements:['id' => '\d+'])]
    public function delete(Athlete $athlete, ManagerRegistry $manager): Response
    {
        $manager->getRepository(Athlete::class)->remove($athlete, true);
        $this->addFlash('success', "L'athlète ne participe plus aux JO 2024");
        return $this->redirectToRoute('app_athlete');
    }
}
