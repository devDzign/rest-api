<?php
/**
 * Created by PhpStorm.
 * User: mc
 * Date: 14/02/2017
 * Time: 15:47
 */

namespace Mc\ApiBundle\Controller;


use FOS\RestBundle\Controller\FOSRestController;
use Mc\ApiBundle\Utils\InternalErrorCodes;
//use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Util;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class AbstractApiController
 *
 * @package ApiBundle\Controller\ApiDoc
 */
abstract class AbstractApiController extends FOSRestController
{
    /**
     * send a success response with data
     *
     * @param array $data
     * @param int   $httpCode
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function sendResponseSuccess($data = array(), $httpCode = Response::HTTP_OK)
    {
        return $this->sendResponse($data, $httpCode);
    }

    /**
     * Envoi une réponse HTTP avec son code (200 par défaut) en JSON
     *
     * @param array $data     tableau de données
     * @param int   $httpCode code http
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function sendResponse($data = array(), $httpCode = Response::HTTP_OK)
    {
        $view = $this->view($data, $httpCode);

        $acceptHeader = $this->get('request_stack')->getCurrentRequest()->headers->get('Accept');
        $format = ($acceptHeader === 'application/xml') ? 'xml' : 'json';
        $view->setFormat($format);

        return $this->handleView($view);
    }

    /**
     * Send many errors
     *
     * @param array $data
     * @param int   $httpCode
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function sendResponseErrors($data = array(), $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        return $this->sendResponse($data, $httpCode);
    }

    /**
     * send one error : message + code
     *
     * @param       $message           message in response JSON
     * @param       $internalErrorCode Error code in response JSON
     * @param int   $httpCode          HTTP Error code
     * @param array $parametres
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function sendResponseError(
        $message,
        $internalErrorCode,
        $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR,
        array $parametres = []
    ) {
        return $this->sendResponse(
            [
                [
                    'message' => $this->get("translator")->trans($message, $parametres),
                    'code'    => $internalErrorCode,
                ],
            ],
            $httpCode
        );
    }

    /**
     * @param FormInterface $form
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function sendResponseFormError(FormInterface $form)
    {
        $errors = $this->getErrors($form);

        if (($form->getData() == null || $form->isEmpty()) && empty($target)) {
            array_unshift(
                $errors,
                [
                    'message' => $this->get("translator")->trans('Form is empty'),
                    'code'    => InternalErrorCodes::FORM_ERROR,
                ]
            );
        }

        return $this->sendResponse($errors, Codes::HTTP_BAD_REQUEST);
    }

    /**
     * @param FormInterface $form
     * @param string        $target
     *
     * @return array
     */
    protected function getErrors(FormInterface $form, $target = '')
    {
        $errors = array();
        $translator = $this->get('translator');

        $formErrorIterator = $form->getErrors();
        foreach ($formErrorIterator as $error) {
            if (!empty($target)) {
                $errors[] = [
                    'message' => $translator->trans($error->getMessage(), $error->getMessageParameters()),
                    'code'    => InternalErrorCodes::FORM_ERROR,
                    'target'  => $target,
                ];
            } else {
                if ('data.username' == $error->getCause()->getPropertyPath()
                    && $error->getCause()->getConstraint() instanceof UniqueEntity
                    && "usernameCanonical" == $error->getCause()->getConstraint()->fields
                ) {
                    continue;
                }
                $errors[] = [
                    'message' => $translator->trans($error->getMessage(), $error->getMessageParameters()),
                    'code'    => InternalErrorCodes::FORM_EXTRA_FIELD,
                ];
            }
        }
        foreach ($form->all() as $key => $child) {
            $errors = array_merge($errors, $this->getErrors($child, (empty($target) ? '' : $target.'.').$key));
        }

        return $errors;
    }

}
