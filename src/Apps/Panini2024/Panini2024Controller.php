<?php

namespace Olz\Apps\Panini2024;

use Olz\Apps\Panini2024\Components\OlzPanini2024\OlzPanini2024;
use Olz\Apps\Panini2024\Components\OlzPanini2024All\OlzPanini2024All;
use Olz\Apps\Panini2024\Components\OlzPanini2024Masks\OlzPanini2024Masks;
use Olz\Apps\Panini2024\Utils\Panini2024Utils;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Panini2024Controller extends AbstractController {
    #[Route('/apps/panini24')]
    public function index(
        Request $request,
        LoggerInterface $logger
    ): Response {
        $html_out = OlzPanini2024::render([]);
        return new Response($html_out);
    }

    #[Route('/apps/panini24/all')]
    public function all(
        Request $request,
        LoggerInterface $logger
    ): Response {
        $html_out = OlzPanini2024All::render([]);
        return new Response($html_out);
    }

    #[Route('/apps/panini24/mask/{mask}')]
    public function masks(
        Request $request,
        LoggerInterface $logger,
        string $mask,
    ): Response {
        $html_out = OlzPanini2024Masks::render(['mask' => $mask]);
        return new Response($html_out);
    }

    #[Route('/apps/panini24/single/{id}.jpg', requirements: ['id' => '\d+'])]
    public function single(
        Request $request,
        LoggerInterface $logger,
        int $id,
    ): Response {
        $out = Panini2024Utils::fromEnv()->renderSingle($id);
        $response = new Response($out);
        $response->headers->set('Content-Type', 'image/jpeg');
        return $response;
    }

    #[Route('/apps/panini24/pdf/{spec}.pdf')]
    public function random(
        Request $request,
        LoggerInterface $logger,
        string $spec,
    ): Response {
        ini_set('memory_limit', '500M');
        set_time_limit(4000);

        $pdf_out = null;
        $random_res = preg_match('/^random-([0-9]+)(-grid)?$/i', $spec, $random_matches);
        if ($random_res) {
            $num = intval($random_matches[1]);
            $options = [
                'grid' => ($random_matches[2] ?? '') === '-grid',
            ];
            $pdf_out = Panini2024Utils::fromEnv()->renderRandom($num, $options);
        }
        $list_res = preg_match('/^([0-9]+,){11}[0-9]+(-grid)?$/i', $spec, $list_matches);
        if ($list_res) {
            $ids = array_map(function ($idstr) {
                return intval($idstr);
            }, explode(',', $list_matches[0]));
            $options = [
                'grid' => ($list_matches[2] ?? '') === '-grid',
            ];
            $pdf_out = Panini2024Utils::fromEnv()->renderPages([
                ['ids' => $ids],
            ], $options);
        }
        if (!$pdf_out) {
            return new Response("Must adhere to spec: random-N | ID,ID,ID,ID,ID,ID,ID,ID,ID,ID,ID,ID");
        }
        $response = new Response($pdf_out);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }
}
