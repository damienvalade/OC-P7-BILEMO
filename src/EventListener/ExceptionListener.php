<?php


namespace App\EventListener;

use JMS\Serializer\Serializer;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $code = $this->getCodeError($exception);

        $result['body'] = [
            'code' => $code,
            'message' => $exception->getMessage()
        ];

        $response = new JsonResponse($result['body'], $code);

        $event->setResponse($response);
    }

    /**
     * @param \Throwable $exception
     * @return int
     */
    public function getCodeError(\Throwable $exception)
    {
        if (!$exception->getCode() && $exception instanceof HttpExceptionInterface) {
            $code = $exception->getStatusCode();
        } elseif (!$exception->getCode()) {
            $code = Response::HTTP_INTERNAL_SERVER_ERROR;
        } else {
            $code = $exception->getCode();
        }
        return $code;
    }

}