<?php

namespace App\Controller;

use App\Entity\SpotifyCredentials;
use App\Entity\SpotifyPlaylists;
use MsgPhp\User\Infrastructure\Security\UserIdentityProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SpotifyController extends PolyfillController
{

    private $session;
    /**
     * SpotifyController constructor.
     * @param UserIdentityProvider $provider
     */
    public function __construct(UserIdentityProvider $provider)
    {
        parent::__construct($provider);

        $this->session = new Session(
            'd5f7258265454a7499616322970c809a','994eebd8f1f34fad992d5b47a6a488a8','http://localhost:8000/spotify/authorize'
        );

    }

    private function spotifyWebApiFactory()
    {
        $credsRepo = $this->getDoctrine()->getRepository("App:SpotifyCredentials");
        $creds = $credsRepo->findOneBy(["user" => $this->getUserDomain()]);

        if ($creds) {
            $this->session->setAccessToken($creds->getAccessToken());
            $this->session->setRefreshToken($creds->getRefreshToken());

            return new SpotifyWebAPI(['auto_refresh' => true], $this->session);
        }

        return false;
    }



    /**
     * @Route("/spotify/account", name="spotify_account")
     * @Security("is_granted('ROLE_USER')")
     */
    public function spotifyAccountConnection()
    {
        $credsRepo = $this->getDoctrine()->getRepository("App:SpotifyCredentials");
        $creds = $credsRepo->findOneBy(["user" => $this->getUserDomain()]);

        $spotifyMe = false;

        /** @var SpotifyCredentials $creds */
        if($creds) {
            $this->session->setAccessToken($creds->getAccessToken());
            $this->session->setRefreshToken($creds->getRefreshToken());

            $api = new SpotifyWebAPI(['auto_refresh' => true], $this->session);

            // Call the API as usual
            $spotifyMe = $api->me();

            // Remember to grab the tokens afterwards, they might have been updated
            $newAccessToken = $this->session->getAccessToken();
            $newRefreshToken = $this->session->getRefreshToken();

            if($creds->getAccessToken() != $newAccessToken || $creds->getRefreshToken() != $newRefreshToken) {
                $creds->setAccessToken($newAccessToken);
                $creds->setRefreshToken($newRefreshToken);
                $this->getDoctrine()->getManager()->flush();
            }
        }

        return $this->render('spotify/connection-status.html.twig', [
            'controller_name' => 'SpotifyController',
            'menu_active' => 'SpotifyController_Connection',
            'spotify_me' => $spotifyMe
        ]);
    }

    /**
     * @Route("/spotify/playlists", name="spotify_playlists", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function spotifyPlaylists()
    {
        $api = $this->spotifyWebApiFactory();

        $storedPlaylists = array();
        if(!$this->getUserDomain()->getSpotifyPlaylists()->isEmpty()) {
            $storedPlaylists = $this->getUserDomain()->getSpotifyPlaylists()->first()->getPlaylists();
        }

        $totalPlaylists = 1;
        $playlists = array();
        for($i = 0; $i <= $totalPlaylists; $i+=50) {
            $playlist = $api->getUserPlaylists($api->me()->id, [
                'limit' => 50,
                'offset' => count($playlists)
            ]);
            $totalPlaylists = $playlist->total;

            foreach($playlist->items as $item) {
                $playlists[] = $item;
            }

        }

        return $this->render('spotify/playlists.html.twig', [
            'controller_name' => 'SpotifyController',
            'menu_active' => 'SpotifyController_Playlists',
            'playlists' => $playlists,
            'storedPlaylists' => $storedPlaylists
        ]);
    }

    /**
     * @Route("/spotify/playlists", name="spotify_save_playlists", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function spotifySavePlaylists(Request $request)
    {
        $playlists = array();
        foreach($request->request->get("playlist") as $p) {
            if(trim($p) != "") {
                $playlists[]= $p;
            }
        }

        if($this->getUserDomain()->getSpotifyPlaylists()->isEmpty()) {
            $pl = new SpotifyPlaylists();
            $pl->setUser($this->getUserDomain());
            $pl->setPlaylists($playlists);
            $this->getDoctrine()->getManager()->persist($pl);
            $this->getDoctrine()->getManager()->flush();

        } else {
            $pl = $this->getUserDomain()->getSpotifyPlaylists()->first();
            $pl->setPlaylists($playlists);
            $this->getDoctrine()->getManager()->flush();
        }

        return new RedirectResponse($this->generateUrl("spotify_playlists"));

    }

    /**
     * @Route("/spotify/player", name="spotify_player")
     * @Security("is_granted('ROLE_USER')")
     */
    public function spotifyPlayer()
    {
        return $this->render('spotify/player.html.twig', [
            'controller_name' => 'SpotifyController',
            'menu_active' => 'SpotifyController_Player'
        ]);
    }

    /**
     * @Route("/spotify/authorize", name="spotify_authorize", methods={"GET"})
     * @Security("is_granted('RROLE_USER')")
     */
    public function spotifyAuthorize(Request $request)
    {

        $options = [
            'scope' => [
                'user-read-playback-state',
                'user-modify-playback-state',
                'user-read-currently-playing',
                'user-read-private',
                'playlist-read-collaborative',
                'playlist-read-private',
                'user-library-read'
            ],
        ];

        if(!$request->query->has('code')) {
            if($request->query->has('error')) {
                return new RedirectResponse($this->generateUrl("spotify_account", ["error" => $request->query->get("error")]));
            } else {
                return new RedirectResponse($this->session->getAuthorizeUrl($options));
            }
        }

        $this->session->requestAccessToken($request->query->get('code'));

        $accessToken = $this->session->getAccessToken();
        $refreshToken = $this->session->getRefreshToken();

        $credsRepo = $this->getDoctrine()->getRepository("App:SpotifyCredentials");
        $creds = $credsRepo->findOneBy(["user" => $this->getUserDomain()]);

        if(!$creds) {
            $creds = new SpotifyCredentials();
            $creds->setUser($this->getUserDomain());
        }

        $creds->setAccessToken($accessToken);
        $creds->setRefreshToken($refreshToken);

        if($creds->getId() == null) {
            $this->getDoctrine()->getManager()->persist($creds);
        }
        $this->getDoctrine()->getManager()->flush();

        return new RedirectResponse($this->generateUrl("spotify_account"));

    }
}
