<?php

namespace Ilios\CoreBundle\Tests\Controller;

use FOS\RestBundle\Util\Codes;
use DateTime;

/**
 * Session controller Test.
 * @package Ilios\CoreBundle\Test\Controller;
 */
class SessionControllerTest extends AbstractControllerTest
{
    /**
     * @return array|string
     */
    protected function getFixtures()
    {
        return [
            'Ilios\CoreBundle\Tests\Fixture\LoadSessionData',
            'Ilios\CoreBundle\Tests\Fixture\LoadSessionDescriptionData',
            'Ilios\CoreBundle\Tests\Fixture\LoadSessionLearningMaterialData',
            'Ilios\CoreBundle\Tests\Fixture\LoadOfferingData',
            'Ilios\CoreBundle\Tests\Fixture\LoadSessionLearningMaterialData',
            'Ilios\CoreBundle\Tests\Fixture\LoadCourseLearningMaterialData',
            'Ilios\CoreBundle\Tests\Fixture\LoadLearningMaterialStatusData',
        ];
    }

    /**
     * @return array|string
     */
    protected function getPrivateFields()
    {
        return [
        ];
    }

    public function testGetSession()
    {
        $session = $this->container
            ->get('ilioscore.dataloader.session')
            ->getOne()
        ;

        $this->createJsonRequest(
            'GET',
            $this->getUrl(
                'get_sessions',
                ['id' => $session['id']]
            )
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $data = json_decode($response->getContent(), true)['sessions'][0];
        $updatedAt = new DateTime($data['updatedAt']);
        unset($data['updatedAt']);
        $this->assertEquals(
            $this->mockSerialize($session),
            $data
        );
        $now = new DateTime();
        $diff = $now->diff($updatedAt);
        $this->assertTrue($diff->y < 1, 'The updatedAt timestamp is within the last year');
    }

    public function testGetAllSessions()
    {
        $this->createJsonRequest('GET', $this->getUrl('cget_sessions'));
        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $data = [];
        $responses = json_decode($response->getContent(), true)['sessions'];
        $now = new DateTime();
        foreach ($responses as $response) {
            $updatedAt = new DateTime($response['updatedAt']);
            unset($response['updatedAt']);
            $diff = $now->diff($updatedAt);
            $this->assertTrue($diff->y < 1, 'The updatedAt timestamp is within the last year');
            $data[] = $response;
        }
        $this->assertEquals(
            $this->mockSerialize(
                $this->container
                    ->get('ilioscore.dataloader.session')
                    ->getAll()
            ),
            $data
        );
    }

    public function testPostSession()
    {
        $data = $this->container->get('ilioscore.dataloader.session')
            ->create();
        $postData = $data;
        //unset any parameters which should not be POSTed
        unset($postData['id']);

        $this->createJsonRequest(
            'POST',
            $this->getUrl('post_sessions'),
            json_encode(['session' => $postData])
        );

        $response = $this->client->getResponse();
        $headers  = [];

        $this->assertEquals(Codes::HTTP_CREATED, $response->getStatusCode(), $response->getContent());
        $responseData = json_decode($response->getContent(), true)['sessions'][0];
        $updatedAt = new DateTime($responseData['updatedAt']);
        unset($responseData['updatedAt']);
        $this->assertEquals(
            $data,
            $responseData,
            $response->getContent()
        );
        $now = new DateTime();
        $diff = $now->diff($updatedAt);
        $this->assertTrue($diff->i < 10, 'The updatedAt timestamp is within the last 10 minutes');
    }

    public function testPostBadSession()
    {
        $invalidSession = $this->container
            ->get('ilioscore.dataloader.session')
            ->createInvalid()
        ;

        $this->createJsonRequest(
            'POST',
            $this->getUrl('post_sessions'),
            json_encode(['session' => $invalidSession])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testPutSession()
    {
        $data = $this->container
            ->get('ilioscore.dataloader.session')
            ->getOne();

        $postData = $data;
        //unset any parameters which should not be POSTed
        unset($postData['id']);

        $this->createJsonRequest(
            'PUT',
            $this->getUrl(
                'put_sessions',
                ['id' => $data['id']]
            ),
            json_encode(['session' => $postData])
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $responseData = json_decode($response->getContent(), true)['session'];
        $updatedAt = new DateTime($responseData['updatedAt']);
        unset($responseData['updatedAt']);
        $this->assertEquals(
            $data,
            $responseData,
            $response->getContent()
        );
        $now = new DateTime();
        $diff = $now->diff($updatedAt);
        $this->assertTrue($diff->i < 10, 'The updatedAt timestamp is within the last 10 minutes');
    }

    public function testDeleteSession()
    {
        $session = $this->container
            ->get('ilioscore.dataloader.session')
            ->getOne()
        ;

        $this->client->request(
            'DELETE',
            $this->getUrl(
                'delete_sessions',
                ['id' => $session['id']]
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->client->request(
            'GET',
            $this->getUrl(
                'get_sessions',
                ['id' => $session['id']]
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testSessionNotFound()
    {
        $this->createJsonRequest(
            'GET',
            $this->getUrl('get_sessions', ['id' => '0'])
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_NOT_FOUND);
    }
    
    /**
     * Grab the first session from the fixtures and get the updatedAt time
     * from the server.
     *
     * @return DateTime
     */
    protected function getSessionUpdatedAt()
    {
        $session = $this->container
            ->get('ilioscore.dataloader.session')
            ->getOne();

        $this->createJsonRequest(
            'GET',
            $this->getUrl(
                'get_sessions',
                ['id' => $session['id']]
            )
        );

        $response = $this->client->getResponse();

        $data = json_decode($response->getContent(), true)['sessions'][0];
        return  new DateTime($data['updatedAt']);
    }
    
    /**
     * Test to see that the updatedAt timestamp has increased by at least one second
     * @param  DateTime $original
     */
    protected function checkUpdatedAtIncreased(DateTime $original)
    {
        $now = $this->getSessionUpdatedAt();
        $diff = $original->diff($now);
        $this->assertTrue(
            $diff->s > 1,
            'The updatedAt timestamp has increased.  Original: ' . $original->format('c') .
            ' Now: ' . $now->format('c')
        );
    }
    
    public function testUpdatingIlmUpdatesSessionStamp()
    {
        $firstUpdatedAt = $this->getSessionUpdatedAt();
        sleep(2); //wait for two seconds
        
        $ilm = $this->container
            ->get('ilioscore.dataloader.ilmsession')
            ->getOne();
        
        $postData = $ilm;
        //unset any parameters which should not be POSTed
        unset($postData['id']);
        $postData['hours'] = $ilm['hours'] + 1;
        $this->createJsonRequest(
            'PUT',
            $this->getUrl(
                'put_ilmsessions',
                ['id' => $ilm['id']]
            ),
            json_encode(['ilmSession' => $postData])
        );
        $this->assertJsonResponse($this->client->getResponse(), Codes::HTTP_OK);
        $this->checkUpdatedAtIncreased($firstUpdatedAt);
    }
    
    public function testUpdatingIlmInstructorUpdatesSessionStamp()
    {
        $firstUpdatedAt = $this->getSessionUpdatedAt();
        sleep(2); //wait for two seconds
        
        $ilm = $this->container
            ->get('ilioscore.dataloader.ilmsession')
            ->getOne();
        
        $postData = $ilm;
        //unset any parameters which should not be POSTed
        unset($postData['id']);
        $postData['instructors'] = ["1", "2"];
        $this->createJsonRequest(
            'PUT',
            $this->getUrl(
                'put_ilmsessions',
                ['id' => $ilm['id']]
            ),
            json_encode(['ilmSession' => $postData])
        );
        $this->assertJsonResponse($this->client->getResponse(), Codes::HTTP_OK);
        $this->checkUpdatedAtIncreased($firstUpdatedAt);
    }
    
    public function testUpdatingIlmInstructorGroupsUpdatesSessionStamp()
    {
        $firstUpdatedAt = $this->getSessionUpdatedAt();
        sleep(2); //wait for two seconds
        
        $ilm = $this->container
            ->get('ilioscore.dataloader.ilmsession')
            ->getOne();
        
        $postData = $ilm;
        //unset any parameters which should not be POSTed
        unset($postData['id']);
        $postData['instructorGroups'] = ["1", "2"];
        $this->createJsonRequest(
            'PUT',
            $this->getUrl(
                'put_ilmsessions',
                ['id' => $ilm['id']]
            ),
            json_encode(['ilmSession' => $postData])
        );
        $this->assertJsonResponse($this->client->getResponse(), Codes::HTTP_OK);
        $this->checkUpdatedAtIncreased($firstUpdatedAt);
    }
    
    public function testUpdatingIlmLearnerGroupsUpdatesSessionStamp()
    {
        $firstUpdatedAt = $this->getSessionUpdatedAt();
        sleep(2); //wait for two seconds
        
        $ilm = $this->container
            ->get('ilioscore.dataloader.ilmsession')
            ->getOne();
        
        $postData = $ilm;
        //unset any parameters which should not be POSTed
        unset($postData['id']);
        $postData['learnerGroups'] = ["1", "2"];
        $this->createJsonRequest(
            'PUT',
            $this->getUrl(
                'put_ilmsessions',
                ['id' => $ilm['id']]
            ),
            json_encode(['ilmSession' => $postData])
        );
        $this->assertJsonResponse($this->client->getResponse(), Codes::HTTP_OK);
        $this->checkUpdatedAtIncreased($firstUpdatedAt);
    }
    
    public function testUpdatingIlmLearnersUpdatesSessionStamp()
    {
        $firstUpdatedAt = $this->getSessionUpdatedAt();
        sleep(2); //wait for two seconds
        
        $ilm = $this->container
            ->get('ilioscore.dataloader.ilmsession')
            ->getOne();
        
        $postData = $ilm;
        //unset any parameters which should not be POSTed
        unset($postData['id']);
        $postData['learners'] = ["1", "2"];
        $this->createJsonRequest(
            'PUT',
            $this->getUrl(
                'put_ilmsessions',
                ['id' => $ilm['id']]
            ),
            json_encode(['ilmSession' => $postData])
        );
        $this->assertJsonResponse($this->client->getResponse(), Codes::HTTP_OK);
        $this->checkUpdatedAtIncreased($firstUpdatedAt);
    }
    
    public function testUpdatingLearningMaterialUpdatesSessionStamp()
    {
        $firstUpdatedAt = $this->getSessionUpdatedAt();
        sleep(2); //wait for two seconds
        
        $lm = $this->container
            ->get('ilioscore.dataloader.learningmaterial')
            ->getOne();
        
        $postData = $lm;
        //unset any parameters which should not be POSTed
        unset($postData['id']);
        $postData['status'] = '2';
        $this->createJsonRequest(
            'PUT',
            $this->getUrl(
                'put_learningmaterials',
                ['id' => $lm['id']]
            ),
            json_encode(['learningMaterial' => $postData])
        );
        $this->assertJsonResponse($this->client->getResponse(), Codes::HTTP_OK);
        $this->checkUpdatedAtIncreased($firstUpdatedAt);
    }
    
    public function testNewSessionLearningMaterialUpdatesSessionStamp()
    {
        $firstUpdatedAt = $this->getSessionUpdatedAt();
        sleep(2); //wait for two seconds
        
        $lm = $this->container
            ->get('ilioscore.dataloader.sessionlearningmaterial')
            ->create();
        
        $postData = $lm;
        //unset any parameters which should not be POSTed
        unset($postData['id']);
        $postData['session'] = '1';
        $this->createJsonRequest(
            'POST',
            $this->getUrl(
                'post_sessionlearningmaterials'
            ),
            json_encode(['sessionLearningMaterial' => $postData])
        );
        $this->assertJsonResponse($this->client->getResponse(), Codes::HTTP_CREATED);
        $this->checkUpdatedAtIncreased($firstUpdatedAt);
    }
    
    public function testUpdatingSessionLearningMaterialUpdatesSessionStamp()
    {
        $firstUpdatedAt = $this->getSessionUpdatedAt();
        sleep(2); //wait for two seconds
        
        $lm = $this->container
            ->get('ilioscore.dataloader.sessionlearningmaterial')
            ->getOne();
        
        $postData = $lm;
        //unset any parameters which should not be POSTed
        unset($postData['id']);
        $postData['required'] = true;
        $this->createJsonRequest(
            'PUT',
            $this->getUrl(
                'put_sessionlearningmaterials',
                ['id' => $lm['id']]
            ),
            json_encode(['sessionLearningMaterial' => $postData])
        );
        $this->assertJsonResponse($this->client->getResponse(), Codes::HTTP_OK);
        $this->checkUpdatedAtIncreased($firstUpdatedAt);
    }
    
    public function testDeletingSessionLearningMaterialUpdatesSessionStamp()
    {
        $firstUpdatedAt = $this->getSessionUpdatedAt();
        sleep(2); //wait for two seconds
        
        $session = $this->container
            ->get('ilioscore.dataloader.session')
            ->getOne();
        
        $this->client->request(
            'DELETE',
            $this->getUrl(
                'delete_sessionlearningmaterials',
                ['id' => $session['sessionLearningMaterials'][0]]
            )
        );
        $this->assertEquals(Codes::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
        $this->checkUpdatedAtIncreased($firstUpdatedAt);
    }
    
    public function testDeletingSessionDescriptionUpdatesSessionStamp()
    {
        $firstUpdatedAt = $this->getSessionUpdatedAt();
        sleep(2); //wait for two seconds
        
        $session = $this->container
            ->get('ilioscore.dataloader.session')
            ->getOne();
        
        $this->client->request(
            'DELETE',
            $this->getUrl(
                'delete_sessionlearningmaterials',
                ['id' => $session['sessionDescription']]
            )
        );
        $this->assertEquals(Codes::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
        $this->checkUpdatedAtIncreased($firstUpdatedAt);
    }
    
    public function testUpdatingSessionDescriptionUpdatesSessionStamp()
    {
        $firstUpdatedAt = $this->getSessionUpdatedAt();
        sleep(2); //wait for two seconds
        
        $lm = $this->container
            ->get('ilioscore.dataloader.sessionDescription')
            ->getOne();
        
        $postData = $lm;
        //unset any parameters which should not be POSTed
        unset($postData['id']);
        $postData['description'] = 'something new';
        $this->createJsonRequest(
            'PUT',
            $this->getUrl(
                'put_sessiondescriptions',
                ['id' => $lm['id']]
            ),
            json_encode(['sessionDescription' => $postData])
        );
        $this->assertJsonResponse($this->client->getResponse(), Codes::HTTP_OK);
        $this->checkUpdatedAtIncreased($firstUpdatedAt);
    }
}