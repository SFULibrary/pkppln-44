<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Deposit;
use AppBundle\Entity\Journal;
use AppBundle\Form\DepositType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Deposit controller.
 *
 * @Security("has_role('ROLE_ADMIN')")
 * @Route("/journal/{journalId}/deposit")
 * @ParamConverter("journal", options={"id"="journalId"})
 */
class DepositController extends Controller
{
    /**
     * Lists all Deposit entities.
     *
     * @param Request $request
     *   Dependency injected HTTP request object.
     *
     * @return array
     *   Array data for the template processor.
     * 
     * @Route("/", name="deposit_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request, Journal $journal)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(Deposit::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();
        $paginator = $this->get('knp_paginator');
        $deposits = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'deposits' => $deposits,
        );
    }
    /**
     * Search for Deposit entities.
     *
     * To make this work, add a method like this one to the 
     * AppBundle:Deposit repository. Replace the fieldName with
     * something appropriate, and adjust the generated search.html.twig
     * template.
     * 
     * <code><pre>
     *    public function searchQuery($q) {
     *        $qb = $this->createQueryBuilder('e');
     *        // Simple search against a field
     *        $qb->where("e.fieldName like '%$q%'");
     *        // Full text matching with Beberlei's Doctrine Extensions for MySQL
     *        // https://github.com/beberlei/DoctrineExtensions/
     *        // $qb->addSelect("MATCH_AGAINST (e.name, :q 'IN BOOLEAN MODE') as score");
     *        // $qb->add('where', "MATCH_AGAINST (e.name, :q 'IN BOOLEAN MODE') > 0.5");
     *        return $qb->getQuery();
     *    }
     * </pre></code>
     * 
     * @param Request $request
     *   Dependency injected HTTP request object.
     * 
     * @Route("/search", name="deposit_search")
     * @Method("GET")
     * @Template()
     */
    public function searchAction(Request $request, Journal $journal)
    {
        $em = $this->getDoctrine()->getManager();
	$repo = $em->getRepository('AppBundle:Deposit');
	$q = $request->query->get('q');
	if($q) {
	    $query = $repo->searchQuery($q);
            $paginator = $this->get('knp_paginator');
            $deposits = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
	} else {
            $deposits = array();
	}

        return array(
            'deposits' => $deposits,
            'q' => $q,
        );
    }

    /**
     * Creates a new Deposit entity.
     *
     * @param Request $request
     *   Dependency injected HTTP request object.
     *
     * @return array|RedirectResponse
     *   Array data for the template processor or a redirect to the Deposit.
     * 
     * @Route("/new", name="deposit_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request, Journal $journal)
    {
        $deposit = new Deposit();
        $form = $this->createForm(DepositType::class, $deposit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($deposit);
            $em->flush();

            $this->addFlash('success', 'The new deposit was created.');
            return $this->redirectToRoute('deposit_show', array('id' => $deposit->getId()));
        }

        return array(
            'deposit' => $deposit,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Deposit entity.
     *
     * @param Deposit $deposit
     *   The Deposit to show.
     *
     * @return array
     *   Array data for the template processor.
     *      
     * @Route("/{id}", name="deposit_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Journal $journal, Deposit $deposit)
    {

        return array(
            'deposit' => $deposit,
        );
    }

    /**
     * Displays a form to edit an existing Deposit entity.
     *
     * 
     * @param Request $request
     *   Dependency injected HTTP request object.
     * @param Deposit $deposit
     *   The Deposit to edit.
     * 
     * @return array|RedirectResponse
     *   Array data for the template processor or a redirect to the Deposit.
     * 
     * @Route("/{id}/edit", name="deposit_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Journal $journal, Deposit $deposit)
    {
        $editForm = $this->createForm(DepositType::class, $deposit);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The deposit has been updated.');
            return $this->redirectToRoute('deposit_show', array('id' => $deposit->getId()));
        }

        return array(
            'deposit' => $deposit,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Deletes a Deposit entity.
     *
     *
     * @param Request $request
     *   Dependency injected HTTP request object.
     * @param Deposit $deposit
     *   The Deposit to delete.
     * 
     * @return array|RedirectResponse
     *   A redirect to the deposit_index.
     * 
     * @Route("/{id}/delete", name="deposit_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, Journal $journal, Deposit $deposit)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($deposit);
        $em->flush();
        $this->addFlash('success', 'The deposit was deleted.');

        return $this->redirectToRoute('deposit_index');
    }
}
