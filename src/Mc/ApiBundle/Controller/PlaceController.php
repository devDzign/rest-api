<?php

namespace Mc\ApiBundle\Controller;

use FOS\RestBundle\Request\ParamFetcher;
use Mc\ApiBundle\Entity\Place;
use Mc\ApiBundle\Form\PlaceType;
use Mc\ApiBundle\Utils\InternalErrorCodes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class PlaceController extends AbstractApiController
{

    /**
     *
     * @ApiDoc(
     *     resource=true,
     *     description="Récupère la liste des lieux de l'application",
     *     output= { "class"=Place::class, "groups"={"place"} },
     * )
     * @Rest\QueryParam(name="offset", requirements="\d+", default="", description="Index de début de la pagination")
     * @Rest\QueryParam(name="limit", requirements="\d+", default="", description="Index de fin de la pagination")
     * @Rest\QueryParam(name="sort", requirements="(asc|desc)", nullable=true, description="Ordre de tri (basé sur le nom)")
     * @Rest\View(serializerGroups={"place"})
     * @return Response
     * */
    public function cgetPlacesAction(Request $request, ParamFetcher $paramFetcher)
    {
        try {
            $offset = $paramFetcher->get('offset');
            $limit = $paramFetcher->get('limit');
            $sort = $paramFetcher->get('sort');

            $em = $this->getDoctrine();
            $places = $em->getRepository("ApiBundle:Place")->myFindAll($limit,$offset, $sort);

            return $this->sendResponseSuccess($places);
        } catch (\Exception $exc) {
            $this->get('logger')->error($exc->getMessage(), ['Trace' => $exc->getTraceAsString()]);

            return $this->sendResponseError("An error occured. Please try again.", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * get content of place <id>
     * @ApiDoc(
     *     resource=true,
     *    description="Récupère la liste des lieux de l'application",
     * )
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
     * @ApiDoc(
     *     resource=true,
     *    description="Récupère la liste des lieux de l'application",
     *     input={"class"=PlaceType::class, "name"=""},
     *      statusCodes = {
     *        201 = "Création avec succès",
     *        400 = "Formulaire invalide"
     *    },
     *    responseMap={
     *         201 = {"class"=Place::class, "groups"={"place"}},
     *         400 = { "class"=PlaceType::class, "fos_rest_form_errors"=true, "name" = ""}
     *    }
     * )
     *
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

                $em = $this->getDoctrine()->getManager();

                foreach ($place->getPrices() as $price) {
                    $price->setPlace($place);
                    $em->persist($price);
                }
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
     * @ApiDoc(
     *     resource=true,
     *    description="Récupère la liste des lieux de l'application",
     * )
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
     * @ApiDoc(
     *     resource=true,
     *    description="Récupère la liste des lieux de l'application",
     * )
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
     *
     * @ApiDoc(
     *     resource=true,
     *    description="Récupère la liste des lieux de l'application",
     * )
     * @Rest\View(serializerGroups={"place"})
     * @param Request $request
     * @return Response
     * */
    public function patchPlacesAction(Request $request, $id)
    {
        $this->updatePlace($request, false);
    }

    /**
     * @ApiDoc(
     *     resource=true,
     *    description="Récupère la liste des lieux de l'application",
     * )
     *
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
