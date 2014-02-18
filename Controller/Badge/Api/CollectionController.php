<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Badge\Api;

use Claroline\CoreBundle\Entity\Badge\BadgeCollection;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\Badge\BadgeCollectionType;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/internalapi/badge_collection")
 */
class CollectionController extends Controller
{
    /**
     * @Route("/", name="claro_badge_collection_add", defaults={"_format" = "json"})
     * @Method({"POST"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function addAction(Request $request, User $user)
    {
        $collection = new BadgeCollection();
        $collection->setUser($user);

        return $this->processForm($request, $collection, "POST");
    }

    /**
     * @Route("/{id}", name="claro_badge_collection_edit", defaults={"_format" = "json"})
     * @Method({"PATCH"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function editAction(Request $request, User $user, BadgeCollection $collection)
    {
        return $this->processForm($request, $collection, "PATCH");
    }

    /**
     * @Route("/{id}", name="claro_badge_collection_delete", defaults={"_format" = "json"})
     * @Method({"DELETE"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function deleteAction(Request $request, User $user, BadgeCollection $collection)
    {
        if ($collection->getUser() !== $user) {
            throw $this->createNotFoundException("Collection not found.");
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($collection);
        $entityManager->flush();

        $view = View::create();
        $view->setStatusCode(204);
        return $this->get("fos_rest.view_handler")->handle($view);
    }

    private function processForm(Request $request, BadgeCollection $collection, $method = "PUT")
    {
        $statusCode = (null === $collection->getId()) ? 201 : 204;

        $form = $this->createForm($this->get("claroline.form.badge.collection"), $collection, array("method" => $method));
//        $form->handleRequest($request);
        $form->submit($request->request->get($form->getName()), 'PATCH' !== $method);

        if ($form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($collection);
            $entityManager->flush();

            $view = View::create();
            $view->setStatusCode($statusCode);

            if (201 === $statusCode) {
                $data = array(
                    'collection' => array(
                        'id'        => $collection->getId(),
                        'name'      => $collection->getName(),
                        'is_shared' => $collection->isIsShared(),
                        'shared_id' => $collection->getSharedId()
                    )
                );

                $view->setData($data);
            }

            return $this->get("fos_rest.view_handler")->handle($view);
        }

        $view = View::create($form, 400);
        return $this->get("fos_rest.view_handler")->handle($view);
    }
}
