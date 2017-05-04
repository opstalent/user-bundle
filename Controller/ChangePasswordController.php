<?php

namespace Opstalent\UserBundle\Controller;

use FOS\UserBundle\Event\{
    FilterUserResponseEvent, FormEvent, GetResponseUserEvent
};
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\{
    JsonResponse, Request
};
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ChangePasswordController
 * @package UserBundle\Controller
 */
class ChangePasswordController extends \FOS\UserBundle\Controller\ChangePasswordController
{
    /**
     * @ApiDoc(
     *  resource=false,
     *  section="Change Password",
     *  description="Change Password",
     *  input="FOS\UserBundle\Form\Type\ChangePasswordFormType",
     *  views = {"default"},
     *  statusCodes={
     *     200="OK",
     *     201="Created",
     *     400="Bad Request",
     *     401="Unauthorized",
     *     403="Forbidden",
     *     404="Not Found",
     *     500="Internal Server Error"
     *  }
     * )
     * @param Request $request
     * @return null|JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function changePasswordAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.change_password.form.factory');

        $form = $formFactory->createForm();
        $form->setData($user);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            throw new \Exception('Form data not found', 400);
        }

        if ($form->isValid()) {
            /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
            $userManager = $this->get('fos_user.user_manager');

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_SUCCESS, $event);

            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                return new JsonResponse([
                    'message' => 'Your password has been changed',
                ]);
            }

            $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        } else {
            throw new \Exception((string)$form->getErrors(true, false), 404);
        }
    }
}
