<?php
namespace Ilios\CoreBundle\Tests\Entity;


use Ilios\CoreBundle\Entity\Authentication;
use Mockery as m;

/**
 * Tests for Entity Authentication
 */
class AuthenticationTest extends EntityBase
{
    /**
     * @var Authentication
     */
    protected $object;

    /**
     * Instantiate a Authentication object
     */
    protected function setUp()
    {
        $this->object = new Authentication;
    }
    

    /**
     * @covers Ilios\CoreBundle\Entity\Authentication::setPersonId
     */
    public function testSetPersonId()
    {
        $this->basicSetTest('personId', 'integer');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Authentication::getPersonId
     */
    public function testGetPersonId()
    {
        $this->basicGetTest('personId', 'integer');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Authentication::setUsername
     */
    public function testSetUsername()
    {
        $this->basicSetTest('username', 'string');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Authentication::getUsername
     */
    public function testGetUsername()
    {
        $this->basicGetTest('username', 'string');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Authentication::setPasswordSha256
     */
    public function testSetPasswordSha256()
    {
        $this->basicSetTest('passwordSha256', 'string');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Authentication::getPasswordSha256
     */
    public function testGetPasswordSha256()
    {
        $this->basicGetTest('passwordSha256', 'string');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Authentication::setUser
     */
    public function testSetUser()
    {
        $this->entitySetTest('user', 'User');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Authentication::getUser
     */
    public function testGetUser()
    {
        $this->entityGetTest('user', 'User');
    }
}