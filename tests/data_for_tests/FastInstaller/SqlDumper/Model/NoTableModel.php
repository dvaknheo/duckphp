<?php
namespace tests_Data_SqlDumper\Model;
use DuckPhp\Foundation\SimpleModelTrait;

class NoTableModel
{
    use SimpleModelTrait;
    public function table()
    {
        return '';
    }
}