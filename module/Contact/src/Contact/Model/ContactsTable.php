<?php

namespace Contact\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Log\Logger;

class ContactsTable extends BaseTable
{
    protected $table = 'contacts';

    public function fetchUserContacts($userId, Select $select = null)
    {
        try {
            if (null === $select)
                $select = new Select();

            $select->from($this->table)
                ->where(array("user_id", $userId));
            $resultSet = $this->tableGateway->selectWith($select);
            $resultSet->buffer();

            return $resultSet;
        } catch (\Exception $e) {
            $this->logger->err($e->getMessage());
            return array();
        }
    }

    public function getContact($id)
    {
        $id = (int)$id;
        $rowSet = $this->tableGateway->select(array('id' => $id));
        $row = $rowSet->current();
        if (!$row) {
            throw new \Exception("Could not find contact: $id");
        }

        return $row;
    }

    public function saveContact($userId, Contact $contact)
    {
        $data = array(
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'email' => $contact->email,
            'phone_number' => $contact->phone_number,
            'alternate_number' => $contact->alternate_number,
            "user_id" => $userId
        );

        $id = (int)$contact->id;
        if ($id == 0) {
            $this->insert($data);
        } else {
            if ($this->getContact($id)) {
                //TODO: check for permissions

                $this->update($data, array('id' => $id));
            } else {
                throw new \Exception('Contact id does not exist');
            }
        }
    }

    public function deleteContact($userId, $id)
    {
        //TODO: check for permissions
        $this->tableGateway->delete(array('id' => $id));
    }
}