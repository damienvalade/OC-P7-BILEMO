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
        $message = "Wrong data sent, please try with good type/name: ";
        foreach ($violations as $violation) {
            $message .= sprintf(
                "Field %s: %s",
                $violation->getPropertyPath(),
                $violation->getMessage()
            );
        }
        throw new HttpException(Response::HTTP_BAD_REQUEST, $message);
    }

    /**
     * @param bool $error
     */
    public function errorAllowed(bool $error = false)
    {
        if ($error === true) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'You are not allowed for this request');
        }
    }
}