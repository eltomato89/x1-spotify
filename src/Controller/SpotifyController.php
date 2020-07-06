<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SpotifyController extends AbstractController
{
    /**
     * @Route("/spotify", name="spotify")
     */
    public function index()
    {
        return $this->render('spotify/connection-status.html.twig', [
            'controller_name' => 'SpotifyController',
            'menu_active' => 'SpotifyController_Connect'
        ]);
    }

    /**
     * @Route("/spotify/account", name="spotify_account")
     */
    public function spotifyAccountConnection()
    {
        return $this->render('spotify/connection-status.html.twig', [
            'controller_name' => 'SpotifyController',
            'menu_active' => 'SpotifyController_Connection'
        ]);
    }

    /**
     * @Route("/spotify/playlists", name="spotify_playlists")
     */
    public function spotifyPlaylists()
    {
        return $this->render('spotify/connection-status.html.twig', [
            'controller_name' => 'SpotifyController',
            'menu_active' => 'SpotifyController_Playlists'
        ]);
    }

    /**
     * @Route("/spotify/player", name="spotify_player")
     */
    public function spotifyPlayer()
    {
        return $this->render('spotify/player.html.twig', [
            'controller_name' => 'SpotifyController',
            'menu_active' => 'SpotifyController_Player'
        ]);
    }
}
