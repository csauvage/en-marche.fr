<?php

namespace AppBundle\Exception;

use AppBundle\Entity\ActivationKey;
use Ramsey\Uuid\UuidInterface;

final class ActivationKeyMismatchException extends ActivationKeyException
{
    private $unexpectedAdherent;

    public function __construct(ActivationKey $key, UuidInterface $unexpectedAdherent, \Exception $previous = null)
    {
        $message = sprintf(
            'Activation key %s cannot be used by the adherent %s but only by adherent %s.',
            $key->getToken(),
            $unexpectedAdherent,
            $key->getAdherent()
        );

        parent::__construct($key, $message, $previous);

        $this->unexpectedAdherent = $unexpectedAdherent;
    }

    public function getUnexpectedAdherent(): UuidInterface
    {
        return $this->unexpectedAdherent;
    }
}
