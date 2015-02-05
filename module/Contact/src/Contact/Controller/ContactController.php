<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Contact\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Contact\Model\Contact;
use Contact\Form\ContactForm;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Iterator as paginatorIterator;
use Zend\Db\Sql\Select;
use Application\Controller\BaseController;

class ContactController extends BaseController
{

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect()->toRoute('zfcuser/login');
        }

        return parent::onDispatch($e);
    }

    public function indexAction()
    {
        $orderBy = $this->params()->fromRoute('order_by', 'first_name');
        $order = $this->params()->fromRoute('order', Select::ORDER_ASCENDING);
        $page = $this->params()->fromRoute('page', 1);
        $letter = $this->params()->fromRoute('letter', "all");
        $itemsPerPage = 10;

        $select = new Select();
        $contacts = $this->getContactTable()->fetchUserContacts($this->getLoggedinUserId(), $select->order($orderBy . ' ' . $order), array("letter" => $letter));

        $contacts->current();
        $paginator = new Paginator(new paginatorIterator($contacts));
        $paginator->setCurrentPageNumber($page)
            ->setItemCountPerPage($itemsPerPage)
            ->setPageRange(7);

        return new ViewModel(array(
            'contacts' => $contacts,
            'order_by' => $orderBy,
            'order' => $order,
            'page' => $page,
            'paginator' => $paginator,
            'letter' => $letter,
            'successMessages' => $this->flashMessenger()->getSuccessMessages(),
            'errorMessages' => $this->flashMessenger()->getErrorMessages()
        ));
    }

    public function addAction()
    {
        $form = new ContactForm();
        $form->get('submit')->setAttribute('value', 'Add');

        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $contact = new Contact();
                $form->setInputFilter($contact->getInputFilter());
                $form->setData($request->getPost());
                if ($form->isValid()) {
                    $contact->exchangeArray($form->getData());
                    $this->getContactTable()->saveContact($this->getLoggedinUserId(), $contact);
                    $this->flashMessenger()->addMessage('Contact has been added successfully');
                    return $this->redirect()->toRoute('contact');
                }
            }

            return array('form' => $form);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('There has been a problem. Please try again.');
            return $this->redirect()->toRoute('contact');
        }
    }

    public function editAction()
    {
        try {
            $contactId = (int)$this->params('id');
            if (!$contactId) {
                return $this->redirect()->toRoute('contact', array('action' => 'add'));
            }
            $contact = $this->getContactTable()->getContact($contactId);

            if ($contact->user_id != $this->getLoggedInUserId()) {
                return $this->redirect()->toRoute('contact', array('action' => 'add'));
            }

            $form = new ContactForm();
            $form->bind($contact);
            $form->get('submit')->setAttribute('value', 'Edit');

            $request = $this->getRequest();
            if ($request->isPost()) {
                $form->setData($request->getPost());

                if ($form->isValid()) {
                    $this->getContactTable()->saveContact($this->getLoggedInUserId(), $contact);
                    $this->flashMessenger()->addMessage('Contact has been edited successfully');
                    return $this->redirect()->toRoute('contact');
                }
            }

            return array(
                'id' => $contactId,
                'form' => $form,
            );
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('There has been a problem. Please try again.');
            return $this->redirect()->toRoute('contact');
        }
    }

    public function deleteAction()
    {
        try {
            $contactId = (int)$this->params('id');
            if (!$contactId) {
                return $this->redirect()->toRoute('contact');
            }

            $request = $this->getRequest();
            if ($request->isPost()) {
                $del = $request->getPost()->get('del', 'No');
                if ($del == 'Yes') {
                    $contactId = (int)$request->getPost()->get('id');
                    $this->getContactTable()->deleteContact($this->getLoggedInUserId(), $contactId);
                    $this->flashMessenger()->addSuccessMessage('Contact has been deleted successfully');
                }

                return $this->redirect()->toRoute('contact');
            }

            return array(
                'id' => $contactId,
                'contact' => $this->getContactTable()->getContact($contactId)
            );
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('There has been a problem. Please try again.');
            return $this->redirect()->toRoute('contact');
        }
    }

    /**
     * @return \Contact\Model\ContactsTable
     */
    public function getContactTable()
    {
        return $this->getServiceLocator()->get('Contact\Model\ContactsTable');
    }
}
