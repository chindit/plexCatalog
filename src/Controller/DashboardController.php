<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ServerType;
use App\Service\CollectionService;
use Chindit\PlexApi\PlexServer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(#[CurrentUser]User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $serverForm = $this->createForm(ServerType::class, $user);

        $serverForm->handleRequest($request);

        $isFaultyForm = false;
        if ($serverForm->isSubmitted()) {
            $isFaultyForm = true;
            if ($serverForm->isValid()) {
                $plex = new PlexServer($user->getServerUrl(), $user->getServerToken(), $user->getServerPort());
                if ($plex->checkConnection()) {
                    $entityManager->flush();
                    $isFaultyForm = false;
                } else {
                    $isFaultyForm = true;
                }
            }
        }

        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'server_form' => $serverForm->createView(),
            'faulty_form' => $isFaultyForm,
        ]);
    }

    #[Route('/sync', name: 'app_sync')]
    public function syncCollection(#[CurrentUser]User $user, CollectionService $collectionService): Response
    {
        $collectionService->syncCollection($user);

        return $this->forward('App\Controller\DashboardController::index');
    }
}
