<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Donation;
use AppBundle\Form\DonationType;
use AppBundle\Intl\UnitedNationsBundle;
use AppBundle\Membership\MembershipRequest;
use AppBundle\Form\MembershipRequestType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MembershipController extends Controller
{
    /**
     * This action enables a guest user to adhere to the community.
     *
     * @Route("/inscription", name="app_membership_register")
     * @Method("GET|POST")
     */
    public function registerAction(Request $request): Response
    {
        $membership = MembershipRequest::createWithCaptcha($request->request->get('g-recaptcha-response'));
        $form = $this->createForm(MembershipRequestType::class, $membership);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adherent = $this->get('app.membership.adherent_factory')->createFromMembershipRequest($membership);
            $em = $this->getDoctrine()->getManager();
            $em->persist($adherent);
            $em->flush();

            $this->addFlash('info', $this->get('translator')->trans('adherent.registration.success'));

            $donation = $this->get('app.donation.factory')->createDonationFromAdherent($adherent);

            $session = $request->getSession();
            $session->set('registering_donation', $donation);
            $session->set('new_adherent_id', $adherent->getId());

            return $this->redirectToRoute('donation_index');
        }

        return $this->render('membership/register.html.twig', [
            'membership' => $membership,
            'form' => $form->createView(),
            'countries' => UnitedNationsBundle::getCountries($request->getLocale()),
        ]);
    }

    /**
     * This action enables a new user to donate as a second step of the
     * registration process, thus he/she may not be logged-in/activated yet.
     *
     * @Route("/inscription/donation", name="app_membership_donate")
     * @Method("GET|POST")
     */
    public function donateAction(Request $request): Response
    {
        if (!$donation = $request->getSession()->remove('registering_donation')) {
            //throw $this->createNotFoundException('The adherent has not been successfully redirected from the registration page.');
        }
        $donation = new Donation();

        $form = $this->createForm(DonationType::class, $donation, [
            'locale' => $request->getLocale(),
            'sponsor_form' => false,
        ])
            // TODO add this field only if anonymous (adherent not activated yet)
            ->add('pass', SubmitType::class)
        ;

        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->has('pass') && $form->get('pass')->isClicked()) {
                return $this->redirectToRoute('app_adherent_pin_interests');
            }

            if ($form->isValid()) {
                $this->get('app.donation.manager')->persist($donation, $request->getClientIp());

                return $this->redirectToRoute('donation_pay', [
                    'id' => $donation->getId()->toString(),
                ]);
            }
        }

        return $this->render('membership/donate.html.twig', [
            'form' => $form->createView(),
            'donation' => $donation,
            'countries' => UnitedNationsBundle::getCountries($request->getLocale()),
        ]);
    }

    /**
     * This action enables a new user to activate his\her newly created
     * membership account.
     *
     * @Route("/inscription/finaliser", name="app_membership_activate")
     * @Method("GET")
     */
    public function activateAction(Request $request): Response
    {
        return new Response('TO BE IMPLEMENTED');
    }
}
