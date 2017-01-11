<?php

namespace AppBundle\Membership;

use AppBundle\Entity\ActivationKey;
use Doctrine\Common\Persistence\ObjectManager;

class MembershipRequestHandler
{
    private $adherentFactory;
    private $manager;

    public function __construct(
        AdherentFactory $adherentFactory,
        /* Notification service here */
        ObjectManager $manager
    ) {
        $this->adherentFactory = $adherentFactory;
        $this->manager = $manager;
    }

    public function handle(MembershipRequest $membershipRequest)
    {
        $adherent = $this->adherentFactory->createFromMembershipRequest($membershipRequest);
        $activationKey = ActivationKey::generate(clone $adherent->getUuid());

        $this->manager->persist($adherent);
        $this->manager->persist($activationKey);
        $this->manager->flush();

        // send notification email with mailjet mailer
    }
}
