<?php

namespace Mc\ApiBundle\Controller;

use Mc\ApiBundle\Entity\Place;
use Mc\ApiBundle\Entity\Price;
use Mc\ApiBundle\Form\PlaceType;
use Mc\ApiBundle\Form\PriceType;
use Mc\ApiBundle\Utils\InternalErrorCodes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class PriceController extends AbstractApiController
{

    /**
     * return List places
     * @Rest\View(serializerGroups={"price"})
     * @return Response
     * */
    public function getPlacesPricesAction($id)
    {
        try {
            $em = $this->getDoctrine();
            $place = $em->getRepository('ApiBundle:Place')
                ->find($id);

            return $this->sendResponseSuccess($place->getPrices());
        } catch (\Exception $exc) {
            $this->get('logger')->error($exc->getMessage(), ['Trace' => $exc->getTraceAsString()]);

            return $this->sendResponseError("An error occured. Please try again.", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     *
     * Create one Place
     *
     * type of Place
     *  <ul>
     *      <li> name</li>
     *      <li> address </li>
     *  </ul>
     * @param Request $request
     * @return Response
     * */
    public function postPlacesPriceAction(Request $request, $id)
    {

        try {

            $em = $this->getDoctrine();
            $place = $em->getRepository('ApiBundle:Place')
                ->find($id);

            /* @var $place Place */

            if (empty($place)) {
                return $this->sendResponseError("La place id: " . $request->get('id') . " n'existe pas ",
                    Response::HTTP_NOT_FOUND,
                    Response::HTTP_NOT_FOUND);
            }

            $price = new Price();
            $price->setPlace($place); // Ici, le lieu est associé au prix
            $form = $this->createForm(PriceType::class, $price);

            // Le paramétre false dit à Symfony de garder les valeurs dans notre
            // entité si l'utilisateur n'en fournit pas une dans sa requête
            $form->submit($request->request->all());

            if ($form->isValid()) {

                $manager = $em->getManager();
                $manager->persist($price);
                $manager->flush();

                return $this->sendResponseSuccess($price, Response::HTTP_CREATED);
            } else {

                return $this->sendResponseSuccess($form, Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $exc) {
            $this->get('logger')->error($exc->getMessage(), ['Trace' => $exc->getTraceAsString()]);

            return $this->sendResponseError("An error occured. Please try again.", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



}
