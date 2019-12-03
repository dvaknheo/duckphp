<?php
namespace Project\Model;

use Project\Base\BaseModel;

class TestModel extends BaseModel
{
    public function foo()
    {
        return DATE(DATE_ATOM);
    }
}
