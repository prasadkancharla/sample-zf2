<?php

namespace Contact\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Contact implements InputFilterAwareInterface
{
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone_number;
    public $alternate_number;
    public $user_id;

    protected $inputFilter;

    /**
     * Used by ResultSet to pass each database row to the entity
     */
    public function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->first_name = (isset($data['first_name'])) ? $data['first_name'] : null;
        $this->last_name = (isset($data['last_name'])) ? $data['last_name'] : null;
        $this->email = (isset($data['email'])) ? $data['email'] : null;
        $this->phone_number = (isset($data['phone_number'])) ? $data['phone_number'] : null;
        $this->alternate_number = (isset($data['alternate_number'])) ? $data['alternate_number'] : null;
        $this->user_id = (isset($data['user_id'])) ? $data['user_id'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $factory = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name' => 'id',
                'required' => true,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'first_name',
                'required' => true,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 100,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'last_name',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 100,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'email',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'EmailAddress',
                        'options' => array(),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'phone_number',
                'required' => true,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'PhoneNumber',
                        'options' => array(),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'alternate_number',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'PhoneNumber',
                        'options' => array(
                            'allow_possible' => true
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'user_id',
                'required' => false,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}