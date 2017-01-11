<?php

namespace AppBundle\Exception;

use Ramsey\Uuid\UuidInterface;

class AdherentException extends \RuntimeException
{
    private $adherent;

    public function __construct(UuidInterface $adherent, $message = '', \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->adherent = $adherent;
    }

    public function getAdherent(): UuidInterface
    {
        return $this->adherent;
    }
}
