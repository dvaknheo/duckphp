<?php
namespace tests_Data_SqlDumper\Model;
use DuckPhp\Foundation\Model\ModelTrait;

class NoTableModel
{
    use ModelTrait;
    public function table()
    {
        return '';
    }
}
