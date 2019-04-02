<?php


namespace App\EventSubscriber;


use App\AppBundle\AppBundle;
use App\Controller\Common\ApiController;
use App\Exception\ApiBadRequestHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RestSubscriber implements EventSubscriberInterface
{
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof ApiController) {
            $request = $event->getRequest();
            if (in_array($request->getMethod(), ["POST", "PUT", "DELETE"])) {
                if($data = json_decode($request->getContent(), true)) {
                    $controller[0]->setIsJson(true);
                    $controller[0]->setData($data);
                }
                else {
                    $controller[0]->setData($request->request->all());
                }
            }
        }
    }

    public function onKernelException(GetResponseForExceptionEvent $event) {

        $e = $event->getException();

        if ($e instanceof ApiBadRequestHttpException) {
            $response = new JsonResponse([
                'error' => true,
                'code' => $e->getStatusCode(),
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
            $response->headers->set('Content-Type', 'application/problem+json');
            $event->setResponse($response);
        }

        return;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::EXCEPTION => 'onKernelException'
        ];
    }
}