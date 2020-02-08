<?php


namespace App\Errors;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolationList;

class Errors
{
    /**
     * @param ConstraintViolationList $violations
     */
    public function errorsConstraint(ConstraintViolationList $violations)
    {
        $message = "Bad field";
        foreach ($violations as $violation) {
            $message = sprintf(
                "Field : %s ; message : %s",
                $violation->getPropertyPath(),
                $violation->getMessage()
            );
        }
        $this->errorCustom(Response::HTTP_BAD_REQUEST, $message);
    }

    public function errorAllowed()
    {

        $this->errorCustom(Response::HTTP_FORBIDDEN, 'You are not allowed for this request');
    }

    public function errorBadRequest()
    {
        $this->errorCustom(Response::HTTP_BAD_REQUEST, 'Wrong data sent, please try with good type/name');

    }

    /**
     * @param int $response
     * @param string $message
     */
    public function errorCustom(int $response, string $message)
    {
        throw new HttpException($response, $message);
    }
}