<?php

namespace GestyMVC\Twig;

use Router;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ParticipantUrlExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('participant_url', [$this, 'participantUrl']),
        ];
    }

    public static function participantUrl($url, $participant_id)
    {
        if (!$participant_id) return $url;

        $token = md5($url . $participant_id . 'email');
        $data = hex_encode(json_encode([
            $participant_id,
            $url,
            $token,
        ]));

        return Router::url(['controller' => 'Pages', 'action' => 'email', $data]);
    }
}
