<?php
namespace Makaira\OxidConnectEssential\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use Symfony\Component\HttpFoundation\JsonResponse;

class ReviewController extends FrontendController
{
    public function getReviews()
    {
        $this->sendResponse([
            'aaa'
        ]);
    }
    private function sendResponse(array $content, int $status = 200)
    {
        $response = new JsonResponse($content, $status);
        $response->send();
    }
}