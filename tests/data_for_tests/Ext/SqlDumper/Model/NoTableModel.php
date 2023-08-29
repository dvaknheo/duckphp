<?php
namespace tests_Data_SqlDumper\Model;
use DuckPhp\Component\SimpleModelTrait;

class NoTableModel
{
    use SimpleModelTrait;
    public function table()
    {
        return '';
    }
}