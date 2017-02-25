<?php

namespace Mc\ApiBundle\Controller;

use Mc\ApiBundle\Entity\Place;
use Mc\ApiBundle\Form\PlaceType;
use Mc\ApiBundle\Utils\InternalErrorCodes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class PlaceController extends AbstractApiController
{

    /**
     * return List places
     * @Rest\View(serializerGroups={"place"})
     * @return Response
     * */
    public function cgetPlacesAction()
    {
        try {
            $em = $this->getDoctrine();
            $places = $em->getRepository("ApiBundle:Place")->findAll();

            return $this->sendResponseSuccess($places);
        } catch (\Exception $exc) {
            $this->get('logger')->error($exc->getMessage(), ['Trace' => $exc->getTraceAsString()]);

            return $this->sendResponseError("An error occured. Please try again.", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * get content of place <id>
     *
     * type of place
     *  <ul>
     *      <li>id place</li>
     *  </ul>
     * @Rest\View(serializerGroups={"place"})
     * @param $id
     * @return Response
     */
    public function getPlaceAction($id)
    {
        try {
            if (empty($id) || $id == "{id}") {

                return $this->sendResponseError("You must give id ",
                    InternalErrorCodes::REQUEST_DATA_ERROR,
                    Response::HTTP_BAD_REQUEST);
            }
            $em = $this->getDoctrine();
            $place = $em->getRepository("ApiBundle:Place")->find($id);

            if (empty($place)) {
                return $this->sendResponseError("l'article id: " . $id . " n'existe pas ",
                    Response::HTTP_NOT_FOUND,
                    Response::HTTP_NOT_FOUND);
            }

            return $this->sendResponseSuccess($place);

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
     * @Rest\View(serializerGroups={"place"})
     * @param Request $request
     * @return Response
     * */
    public function cpostPlacesAction(Request $request)
    {

        try {
            $place = new Place();
            $form = $this->createForm(
                PlaceType::class,    // FormType
                $place          // Entity
            );

            $form->submit($request->request->all());

            if ($form->isValid()) {

                $em = $this->get('doctrine.orm.entity_manager');
                $em->persist($place);
                $em->flush();

                return $this->sendResponseSuccess($place, Response::HTTP_CREATED);
            } else {

                return $this->sendResponseSuccess($form, Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $exc) {
            $this->get('logger')->error($exc->getMessage(), ['Trace' => $exc->getTraceAsString()]);

            return $this->sendResponseError("An error occured. Please try again.", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Remove one Place
     *
     * type of place
     *  <ul>
     *      <li> title</li>
     *      <li> body </li>
     *  </ul>
     *
     * @Rest\View(serializerGroups={"place"})
     * @return Response
     * */
    public function deletePlaceAction($id)
    {

        try {

            $em = $this->getDoctrine();

            if (empty($id) || $id == "{id}") {
                return $this->sendResponseError("You must give id ",
                    Response::HTTP_NOT_FOUND,
                    Response::HTTP_NOT_FOUND);
            }

            $place = $em->getRepository("ApiBundle:Place")->find($id);

            if (empty($place)) {
                return $this->sendResponseError("la place id: " . $id . " n'existe pas ",
                    Response::HTTP_NOT_FOUND,
                    Response::HTTP_NOT_FOUND);
            }

            $manager = $em->getManager();
            foreach ($place->getPrices() as $price) {
                $manager->remove($price);
            }

            $manager->remove($place);
            $manager->flush();

            return $this->sendResponseSuccess($place, Response::HTTP_NO_CONTENT);
        } catch (\Exception $exc) {
            $this->get('logger')->error($exc->getMessage(), ['Trace' => $exc->getTraceAsString()]);

            return $this->sendResponseError("An error occured. Please try again.", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *
     * Put Update one place
     *
     * @Rest\View(serializerGroups={"place"})
     * @param Request $request
     * @return Response
     * */
    public function putPlacesAction(Request $request, $id)
    {
        return $this->updatePlace($request, true);
    }

    /**
     *
     * patch Partial Update one place
     * @Rest\View(serializerGroups={"place"})
     * @param Request $request
     * @return Response
     * */
    public function patchPlacesAction(Request $request, $id)
    {
        $this->updatePlace($request, false);
    }

    /**
     * @param Request $request
     * @param $clearMissing
     * @return Response
     */
    private function updatePlace(Request $request, $clearMissing)
    {
        try {
            $em = $this->get('doctrine.orm.entity_manager');
            $place = $em
                ->getRepository('ApiBundle:Place')
                ->find($request->get('id'));

            if (empty($place)) {
                return $this->sendResponseError("La place id: " . $request->get('id') . " n'existe pas ",
                    Response::HTTP_NOT_FOUND,
                    Response::HTTP_NOT_FOUND);
            }

            $form = $this->createForm(
                PlaceType::class,    // FormType
                $place          // Entity
            );

            $form->submit($request->request->all(),$clearMissing);

            if ($form->isValid()) {
                // l'entité vient de la base, donc le merge n'est pas nécessaire.
                // il est utilisé juste par soucis de clarté
                $em->merge($place);
                $em->flush();

                return $this->sendResponseSuccess($place, Response::HTTP_OK);
            } else {

                return $this->sendResponseSuccess($form);
            }
        } catch (\Exception $exc) {
            $this->get('logger')->error($exc->getMessage(), ['Trace' => $exc->getTraceAsString()]);
            return $this->sendResponseError("An error occured. Please try again.", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
