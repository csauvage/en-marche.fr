<?php

namespace AppBundle\Exception;

use Ramsey\Uuid\UuidInterface;

final class AdherentAlreadyEnabledException extends AdherentException
{
    public function __construct(UuidInterface $adherent, \Exception $previous = null)
    {
        parent::__construct(
            $adherent,
            sprintf('Adherent "%s" is already enabled.', $adherent),
            $previous
        );
    }
}
