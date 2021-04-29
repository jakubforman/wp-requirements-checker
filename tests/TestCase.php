<?php
namespace jayjay666\WPRequirementsChecker\Tests;

use jayjay666\WPRequirementsChecker\Validator;
use ReflectionException;
use WP_UnitTestCase;
require __DIR__ . '/bootstrap.php';

class TestCase extends WP_UnitTestCase
{
    /**
     * @var Validator
     */
    var $validator;

    public function setUp()
    {
        parent::setUp();
        $this->validator = new Validator("7.1",'test-plugin','test-plugin');

    }

    public function tearDown()
    {
        // delete your instance
        unset($this->validator);
        parent::tearDown();
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object $object Instantiated object that we will run method on.
     * @param string $method_name Method name to call.
     * @param array $parameters Array of parameters to pass into method.
     * @param string $classname Optional. The class to use for accessing private properties.
     *
     * @return mixed Method return.
     * @throws ReflectionException
     */
    protected function invoke_method($object, $method_name, array $parameters = [], $classname = '')
    {
        if (empty($classname)) {
            $classname = get_class($object);
        }

        $reflection = new \ReflectionClass($classname);
        $method = $reflection->getMethod($method_name);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param string $classname A class that we will run the method on.
     * @param string $method_name Method name to call.
     * @param array $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     * @throws ReflectionException
     */
    protected function invoke_static_method($classname, $method_name, array $parameters = [])
    {
        $reflection = new \ReflectionClass($classname);
        $method = $reflection->getMethod($method_name);
        $method->setAccessible(true);

        return $method->invokeArgs(null, $parameters);
    }

    /**
     * Sets the value of a private/protected property of a class.
     *
     * @param string $classname A class whose property we will access.
     * @param string $property_name Property to set.
     * @param mixed|null $value The new value.
     * @throws ReflectionException
     */
    protected function set_static_value($classname, $property_name, $value)
    {
        $reflection = new \ReflectionClass($classname);
        $property = $reflection->getProperty($property_name);
        $property->setAccessible(true);
        $property->setValue($value);
    }

    /**
     * Sets the value of a private/protected property of a class.
     *
     * @param object $object Instantiated object that we will run method on.
     * @param string $property_name Property to set.
     * @param mixed|null $value The new value.
     * @param string $classname Optional. The class to use for accessing private properties.
     * @throws ReflectionException
     */
    protected function set_value($object, $property_name, $value, $classname = '')
    {
        if (empty($classname)) {
            $classname = get_class($object);
        }

        $reflection = new \ReflectionClass($classname);
        $property = $reflection->getProperty($property_name);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * Retrieves the value of a private/protected property of a class.
     *
     * @param string $classname A class whose property we will access.
     * @param string $property_name Property to set.
     *
     * @return mixed
     * @throws ReflectionException
     */
    protected function get_static_value($classname, $property_name)
    {
        $reflection = new \ReflectionClass($classname);
        $property = $reflection->getProperty($property_name);
        $property->setAccessible(true);

        return $property->getValue();
    }

    /**
     * Retrieves the value of a private/protected property of a class.
     *
     * @param object $object Instantiated object that we will run method on.
     * @param string $property_name Property to set.
     * @param string $classname Optional. The class to use for accessing private properties.
     *
     * @return mixed
     * @throws ReflectionException
     */
    protected function get_value($object, $property_name, $classname = '')
    {
        if (empty($classname)) {
            $classname = get_class($object);
        }

        $reflection = new \ReflectionClass($classname);
        $property = $reflection->getProperty($property_name);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * Reports an error identified by $message if $attribute in $object does not have the $key.
     *
     * @param string $key The array key.
     * @param string $attribute The attribute name.
     * @param object $object The object.
     * @param string $message Optional. Default ''.
     * @throws ReflectionException
     */
    protected function assert_attribute_array_has_key($key, $attribute, $object, $message = '')
    {
        $ref = new \ReflectionClass(get_class($object));
        $prop = $ref->getProperty($attribute);
        $prop->setAccessible(true);

        return $this->assertArrayHasKey($key, $prop->getValue($object), $message);
    }

    /**
     * Reports an error identified by $message if $attribute in $object does have the $key.
     *
     * @param string $key The array key.
     * @param string $attribute The attribute name.
     * @param object $object The object.
     * @param string $message Optional. Default ''.
     * @throws ReflectionException
     */
    protected function assert_attribute_array_not_has_key($key, $attribute, $object, $message = '')
    {
        $ref = new \ReflectionClass(get_class($object));
        $prop = $ref->getProperty($attribute);
        $prop->setAccessible(true);

        return $this->assertArrayNotHasKey($key, $prop->getValue($object), $message);
    }
}