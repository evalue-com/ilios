<?php

namespace Tests\CoreBundle\DataLoader;

class DepartmentData extends AbstractDataLoader
{
    protected function getData()
    {
        $arr = array();

        $arr[] = array(
            'id' => 1,
            'title' => $this->faker->text(50),
            'school' => '1',
            'stewards' => ['1']
        );

        $arr[] = array(
            'id' => 2,
            'title' => $this->faker->text(50),
            'school' => '1',
            'stewards' => ['2'],
            'stewards' => ['2']
        );


        return $arr;
    }

    public function create()
    {
        return array(
            'id' => 3,
            'title' => $this->faker->text(50),
            'school' => '1',
            'stewards' => []
        );
    }

    public function createInvalid()
    {
        return [];
    }
}
