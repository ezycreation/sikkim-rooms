<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Banner;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

/**
 * Banner controller.
 *
 * @Route("banner")
 */
class BannerController extends Controller
{
    /**
     * Lists all banner entities.
     *
     * @Route("/", name="banner_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $banners = $em->getRepository('AppBundle:Banner')->findAll();

        return $this->render('banner/index.html.twig', array(
            'banners' => $banners,
        ));
    }

    /**
     * Creates a new banner entity.
     *
     * @Route("/new", name="banner_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $banner = new Banner();
        $form = $this->createForm('AppBundle\Form\BannerType', $banner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $banner->getImageurl();
            $fileName = md5(uniqid()).'.'.$file->guessExtension();
            $file->move(
                $this->getParameter('banner'),
                $fileName
            );
            $banner->setImageurl($fileName);
            $em = $this->getDoctrine()->getManager();
            $em->persist($banner);
            $em->flush();

            return $this->redirectToRoute('banner_show', array('id' => $banner->getId()));
        }

        return $this->render('banner/new.html.twig', array(
            'banner' => $banner,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a banner entity.
     *
     * @Route("/{id}", name="banner_show")
     * @Method("GET")
     */
    public function showAction(Banner $banner)
    {
        $deleteForm = $this->createDeleteForm($banner);

        return $this->render('banner/show.html.twig', array(
            'banner' => $banner,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing banner entity.
     *
     * @Route("/{id}/edit", name="banner_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Banner $banner)
    {
        $fileName=$banner->getImageurl();
        $deleteForm = $this->createDeleteForm($banner);
        $banner->setImageurl(
            new File($this->getParameter('banner').'/'.$banner->getImageurl())
        );
        $editForm = $this->createForm('AppBundle\Form\BannerType', $banner);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $file = $banner->getImageurl();
            if ($file)
            {
                $file_path='uploads/banner/'.$fileName;
                unlink($file_path);
                $fileName1 = md5(uniqid()).'.'.$file->guessExtension();
                $file->move(
                    $this->getParameter('banner'),
                    $fileName1
                );
                $banner->setImageurl($fileName1);

            }
            else
            {
                $banner->setImageurl($banner);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($banner);
            $em->flush();
            $request->getSession()
                ->getFlashBag()
                ->add('success', 'Banner image has been successfully updated !');
            return $this->redirectToRoute('banner_index', array('id' => $banner->getId()));
        }

        return $this->render('banner/edit.html.twig', array(
            'banner' => $banner,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a banner entity.
     *
     * @Route("/{id}", name="banner_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Banner $banner)
    {
        $form = $this->createDeleteForm($banner);
        $form->handleRequest($request);
        $fileName=$banner->getImageurl();

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if($fileName)
            {
                $file_path='uploads/banner/'.$fileName;
                unlink($file_path);
            }
            $em->remove($banner);
            $em->flush();
        }

        return $this->redirectToRoute('banner_index');
    }

    /**
     * Creates a form to delete a banner entity.
     *
     * @param Banner $banner The banner entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Banner $banner)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('banner_delete', array('id' => $banner->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

}
