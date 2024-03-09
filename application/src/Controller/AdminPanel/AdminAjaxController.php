<?php

namespace App\Controller\AdminPanel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAjaxController extends AbstractController
{
    /**
     * Re-write url
     *
     * @return JsonResponse
     */
    public function generateSlug(Request $request): JsonResponse
    {
        $text  = (string) $request->query->get('text');

        $text = trim(mb_strtolower($text));
        $text = str_replace(["_", " ", "/", "\n", "\r", "\t"], "-", $text);
        $text = str_replace("&", "and", $text);
        $text = preg_replace("/[^a-z0-9\-\.]/", "", $text);
        $text = preg_replace("/-{2,}/", "-", $text);

        return new JsonResponse(
            json_encode($text ?? []),
            Response::HTTP_OK,
            [],
            true
        );
    }
}
