<?php

namespace Opstalent\UserBundle\Controller;

use AppBundle\Entity\User;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ResettingController
 * @package UserBundle\Controller
 */
class ResettingController extends \FOS\UserBundle\Controller\ResettingController
{
    /**
     * @ApiDoc(
     *  resource=false,
     *  section="Resetting password",
     *  description="Request reset user password: send email to user",
     *  parameters={
     *      {"name"="email", "dataType"="string", "required"=true, "description"="Email"},
     *      {"name"="url", "dataType"="string", "required"=true, "description"="url"}
     *  },
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
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function sendEmailAction(Request $request)
    {
        $email = $request->request->get('email');
        $url = $request->request->get('url');
        /** @var User $user */
        $user = $this->get('fos_user.user_manager')->findUserByEmail($email);
        if (null === $user) {
            throw new \Exception('invalid_email', 404);
        }

        $ttlToken = $this->container->getParameter('fos_user.resetting.token_ttl');
        if ($user->isPasswordRequestNonExpired($ttlToken)) {
            throw new \Exception('password.already.requested', 400);
        }

        if (null === $user->getConfirmationToken()) {
            $tokenGenerator = $this->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }


        $template = $this->getParameter('fos_user.resetting.email.template');
        $from = $this->getParameter('mailer_email');
        $this->get('mailer')->sendEmail($user->getEmail(), $from, 'Abodoo - password reset', $template, [
            'user' => $user,
            'confirmationUrl' => $url . '/' . $user->getConfirmationToken(),
        ]);

        $user->setPasswordRequestedAt(new \DateTime());
        $this->get('fos_user.user_manager')->updateUser($user);

        return new JsonResponse([
            'success' => true
        ]);
    }

    /**
     * @ApiDoc(
     *  resource=false,
     *  section="Resetting password",
     *  description="Request reset user password: send email to user",
     *  parameters={
     *      {"name"="fos_user_resetting_form[plainPassword][first]", "dataType"="string", "required"=true, "description"="New password"},
     *      {"name"="fos_user_resetting_form[plainPassword][second]", "dataType"="string", "required"=true, "description"="New password confirmation"}
     *  },
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
     *
     * @param Request $request
     * @param string $token
     * @return JsonResponse
     * @throws \Exception
     */
    public function resetAction(Request $request, $token)
    {
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.resetting.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->findUserByConfirmationToken($token);
        if (null === $user) {
            throw new \Exception(sprintf('The user with "confirmation token" does not exist for value "%s"', $token), 404);
        }

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            throw new \Exception('Form data not found', 400);
        }

        if ($form->isValid()) {
            $user->setPasswordRequestedAt(null);
            $userManager->updateUser($user);
            return new JsonResponse([
                'success' => true,
            ]);
        }

        throw new \Exception((string)$form->getErrors(true, false), 400);
    }
}
