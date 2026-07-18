<?php
namespace tests_Data_SqlDumper\Model;
use DuckPhp\Foundation\ModelTrait;

class NoTableModel
{
    use ModelTrait;
    public function table()
    {
        return '';
    }
}