<?php


namespace App\EventListener;

use Psr\Log\LoggerInterface;
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

        $message = sprintf(
            "Error message : \"%s\" code: %s",
            $exception->getMessage(),
            $code
        );

        $this->logger->error($message);
        $response = $this->getResponse($message, $exception);
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

    /**
     * @param string $message
     * @param \Throwable $exception
     * @return Response
     */
    public function getResponse(string $message, \Throwable $exception)
    {
        $response = new Response();
        $response->setContent($message);

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $response->headers->replace($exception->getHeaders());
        }

        if (empty($statusCode)) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        $response->setStatusCode($statusCode);
        return $response;
    }
}