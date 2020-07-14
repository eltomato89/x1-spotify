<?php

namespace App\Controller;

use App\Entity\SpotifyCredentials;
use App\Entity\SpotifyPlaylists;
use MsgPhp\User\Infrastructure\Security\UserIdentityProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Json;

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
            'd5f7258265454a7499616322970c809a', '994eebd8f1f34fad992d5b47a6a488a8', 'http://localhost:8000/spotify/authorize'
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

        try {
            /** @var SpotifyCredentials $creds */
            if ($creds) {
                $this->session->setAccessToken($creds->getAccessToken());
                $this->session->setRefreshToken($creds->getRefreshToken());

                $api = new SpotifyWebAPI(['auto_refresh' => true], $this->session);

                // Call the API as usual
                $spotifyMe = $api->me();

                // Remember to grab the tokens afterwards, they might have been updated
                $newAccessToken = $this->session->getAccessToken();
                $newRefreshToken = $this->session->getRefreshToken();

                if ($creds->getAccessToken() != $newAccessToken || $creds->getRefreshToken() != $newRefreshToken) {
                    $creds->setAccessToken($newAccessToken);
                    $creds->setRefreshToken($newRefreshToken);
                    $this->getDoctrine()->getManager()->flush();
                }
            }
        } catch (\Exception $e) {

        } finally {
            return $this->render('spotify/connection-status.html.twig', [
                'controller_name' => 'SpotifyController',
                'menu_active' => 'SpotifyController_Connection',
                'spotify_me' => $spotifyMe,
                'user' => $this->getUserDomain()
            ]);
        }

    }

    /**
     * @Route("/spotify/playlists", name="spotify_playlists", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function spotifyPlaylists()
    {
        $api = $this->spotifyWebApiFactory();

        try {
            $api->me();
        } catch (\Exception $e) {
            return new RedirectResponse($this->generateUrl("spotify_account", ["error" => "account_not_connected"]));
        }

        $storedPlaylists = array();
        if (!$this->getUserDomain()->getSpotifyPlaylists()->isEmpty()) {
            $storedPlaylists = $this->getUserDomain()->getSpotifyPlaylists()->first()->getPlaylists();
        }

        $totalPlaylists = 1;
        $playlists = array();
        for ($i = 0; $i <= $totalPlaylists; $i += 50) {
            $playlist = $api->getUserPlaylists($api->me()->id, [
                'limit' => 50,
                'offset' => count($playlists)
            ]);
            $totalPlaylists = $playlist->total;

            foreach ($playlist->items as $item) {
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
        foreach ($request->request->get("playlist") as $p) {
            if (trim($p) != "") {
                $playlists[] = $p;
            }
        }

        if ($this->getUserDomain()->getSpotifyPlaylists()->isEmpty()) {
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
        try {
            $devices = $this->spotifyWebApiFactory()->getMyDevices();

            return $this->render('spotify/player.html.twig', [
                'controller_name' => 'SpotifyController',
                'menu_active' => 'SpotifyController_Player',
                'spotifyDevices' => $devices->devices
            ]);

        } catch (\Exception $e) {
            return new RedirectResponse($this->generateUrl("spotify_account", ["error" => "account_not_connected"]));
        }
    }

    /**
     * @Route("/spotify/authorize", name="spotify_authorize", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
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

        if (!$request->query->has('code')) {
            if ($request->query->has('error')) {
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

        if (!$creds) {
            $creds = new SpotifyCredentials();
            $creds->setUser($this->getUserDomain());
        }

        $creds->setAccessToken($accessToken);
        $creds->setRefreshToken($refreshToken);

        if ($creds->getId() == null) {
            $this->getDoctrine()->getManager()->persist($creds);
        }
        $this->getDoctrine()->getManager()->flush();

        return new RedirectResponse($this->generateUrl("spotify_account"));

    }

    /**
     * @Route("/spotify/api/{player}/state", name="spotify_api_state", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function spotifyApiState(Request $request, $player)
    {
        $api = $this->spotifyWebApiFactory();

        $deviceState = false;
        foreach ($api->getMyDevices()->devices as $device) {
            if ($device->id == $player) {
                $deviceState = $device;
                break;
            }
        }

        $playbackInfo = $api->getMyCurrentPlaybackInfo();
        if ($playbackInfo == null) $playbackInfo = false;

        $playback = array("title" => "", "artist" => "", "album" => "");

        if ($playbackInfo != false && $playbackInfo->currently_playing_type == "track") {
            $artists = array();
            if ($playbackInfo != false) {
                foreach ($playbackInfo->item->artists as $a) {
                    $artists[] = $a->name;
                }
            }

            $playback["artist"] = implode(", ", array_reverse($artists));
            $playback["title"] = $playbackInfo->item->name;
            $playback["album"] = $playbackInfo->item->album->name;
            $playback["cover"] = $playbackInfo->item->album->images[0]->url;
        }

        if ($playbackInfo != false && $playbackInfo->currently_playing_type == "episode") {

            $playback["artist"] = "";
            $playback["title"] = "EPISODE";
            $playback["album"] = "";
            $playback["cover"] = "";
        }

        $currentPlaylist = array(
            "name" => "",
            "id" => "",
            "index" => -1
        );

        if(count($this->getUserDomain()->getSpotifyPlaylists()) > 0 && $playbackInfo != false && $playbackInfo->currently_playing_type == "track" && $playbackInfo->context != null) {
            $userPlaylists = $this->getUserDomain()->getSpotifyPlaylists()->first()->getPlaylists();
            for($i = 0; $i <= count($userPlaylists)-1; $i++) {
                $playlistId = current(array_reverse(explode(":", $playbackInfo->context->uri)));

                if($userPlaylists[$i] == $playlistId) {
                    $playlistInfo = $api->getPlaylist($playlistId);
                    $currentPlaylist["index"] = $i+1;
                    $currentPlaylist["name"] = $playlistInfo->name;
                    $currentPlaylist["id"] = $playlistId;
                    break;
                }
            }
        }


        return new JsonResponse(
            array(
                "player" => $player,
                "player_name" => ($deviceState != false) ? $deviceState->name : false,
                "playing" => ($playbackInfo != false) ? $playbackInfo->is_playing : false,
                "shuffle" => ($playbackInfo != false) ? $playbackInfo->shuffle_state : false,
                "repeat" => ($playbackInfo != false) ? $playbackInfo->repeat_state : false,
                "volume_percent" => ($deviceState != false) ? $deviceState->volume_percent : 0,
                "mute" => $deviceState != false && $deviceState->volume_percent == 0,
                "playback" => $playback,
                "current_playlist" => $currentPlaylist
            )
        );
    }

    /**
     * @Route("/spotify/api/{player}/play", name="spotify_api_play", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function spotifyApiPlay(Request $request, $player)
    {
        $api = $this->spotifyWebApiFactory();
        $result = $api->play($player);

        if($result) {
            return new JsonResponse("OK", 200);
        } else {
            return new JsonResponse("NOK", 503);
        }
    }

    /**
     * @Route("/spotify/api/{player}/pause", name="spotify_api_pause", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function spotifyApiPause(Request $request, $player)
    {
        $api = $this->spotifyWebApiFactory();
        $result = $api->pause($player);

        if($result) {
            return new JsonResponse("OK", 200);
        } else {
            return new JsonResponse("NOK", 503);
        }
    }

    /**
     * @Route("/spotify/api/{player}/next-track", name="spotify_api_next_track", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function spotifyApiNextTrack(Request $request, $player)
    {
        $api = $this->spotifyWebApiFactory();

        $result = $api->next($player);

        if($result) {
            return new JsonResponse("OK", 200);
        } else {
            return new JsonResponse("NOK", 503);
        }
    }

    /**
     * @Route("/spotify/api/{player}/previous-track", name="spotify_api_prev_track", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function spotifyApiPrevTrack(Request $request, $player)
    {
        $api = $this->spotifyWebApiFactory();

        $result = $api->previous($player);

        if($result) {
            return new JsonResponse("OK", 200);
        } else {
            return new JsonResponse("NOK", 503);
        }
    }

    private function spotifyGetCurrentPlaylistIndex() {

        $api = $this->spotifyWebApiFactory();
        $playbackInfo = $api->getMyCurrentPlaybackInfo();

        if(count($this->getUserDomain()->getSpotifyPlaylists()) > 0 && $playbackInfo != false && $playbackInfo->currently_playing_type == "track" && $playbackInfo->context != null) {
            $userPlaylists = $this->getUserDomain()->getSpotifyPlaylists()->first()->getPlaylists();
            for($i = 0; $i <= count($userPlaylists)-1; $i++) {
                $playlistId = current(array_reverse(explode(":", $playbackInfo->context->uri)));
                if($userPlaylists[$i] == $playlistId) {
                    return $i+1;
                }
            }
        }

        return -1;
    }

    /**
     * @Route("/spotify/api/{player}/next-playlist", name="spotify_api_next_playlist", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function spotifyApiNextPlaylist(Request $request, $player)
    {
        $api = $this->spotifyWebApiFactory();
        $i = $this->spotifyGetCurrentPlaylistIndex();

        if($this->getUserDomain()->getSpotifyPlaylists()->count() != 1) {
            return new JsonResponse(["status" => "NOK", "reason" => "User has not specified any playlists!"]);
        }

        $userPlaylists = $this->getUserDomain()->getSpotifyPlaylists()->first()->getPlaylists();

        if($i+1 > count($userPlaylists)) {
            $nextPlaylist = $userPlaylists[0];
        } else {
            $nextPlaylist = $userPlaylists[$i];
        }

        $api->play($player, ["context_uri" => "spotify:playlist:$nextPlaylist"]);

        return new JsonResponse(["status" => "OK"]);
    }

    /**
     * @Route("/spotify/api/{player}/previous-playlist", name="spotify_api_prev_playlist", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function spotifyApiPrevPlaylist(Request $request, $player)
    {
        $api = $this->spotifyWebApiFactory();
        $i = $this->spotifyGetCurrentPlaylistIndex();

        if($this->getUserDomain()->getSpotifyPlaylists()->count() != 1) {
            return new JsonResponse(["status" => "NOK", "reason" => "User has not specified any playlists!"]);
        }

        $userPlaylists = $this->getUserDomain()->getSpotifyPlaylists()->first()->getPlaylists();

        if( $i <= 1) {
            $nextPlaylist = $userPlaylists[count($userPlaylists)-1];
        } else {
            $nextPlaylist = $userPlaylists[$i-2]; //-1 for prev playlist + -1 for counting from 1
        }

        $api->play($player, ["context_uri" => "spotify:playlist:$nextPlaylist"]);

        return new JsonResponse(["status" => "OK"]);
    }

    /**
     * @Route("/spotify/api/{player}/set-repeat", name="spotify_api_set_repeat", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function spotifyApiSetRepeat(Request $request, $player)
    {
        $state = "off";
        if ($content = $request->getContent()) {
            $parameters = json_decode($content, true);

            if(isset($parameters["state"])) {
                $state = $parameters["state"];
            }
        }

        $api = $this->spotifyWebApiFactory();
        $result = $api->repeat([
            'state' => $state,
            'device_id' => $player
        ]);

        if($result) {
            return new JsonResponse("OK", 200);
        } else {
            return new JsonResponse("NOK", 503);
        }
    }

    /**
     * @Route("/spotify/api/{player}/set-shuffle", name="spotify_api_set_shuffle", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function spotifyApiSetShuffle(Request $request, $player)
    {
        $state = false;
        if ($content = $request->getContent()) {
            $parameters = json_decode($content, true);

            if(isset($parameters["state"])) {
                $state = $parameters["state"] == "true" || $parameters["state"] == true;
            }
        }

        $api = $this->spotifyWebApiFactory();
        $result = $api->shuffle([
            'state' => $state,
            'device_id' => $player
        ]);

        if($result) {
            return new JsonResponse("OK", 200);
        } else {
            return new JsonResponse("NOK", 503);
        }

    }

    /**
     * @Route("/spotify/api/{player}/set-volume", name="spotify_api_set_volume", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function spotifyApiSetVolume(Request $request, $player)
    {
        // POST 1 to 100
        $state = 0;
        if ($content = $request->getContent()) {
            $parameters = json_decode($content, true);

            if(isset($parameters["state"])) {
                $state = intval($parameters["state"]);
            }
        }

        $api = $this->spotifyWebApiFactory();
        $result = $api->changeVolume([
            "volume_percent" => $state,
            "device_id" => $player
        ]);

        if($result) {
            return new JsonResponse("OK", 200);
        } else {
            return new JsonResponse("NOK", 503);
        }

    }

}