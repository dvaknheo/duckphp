<?php
namespace DNMVCS;
interface DBInterface
{
	public function close();
	public function getPDO();
	public function quote($string);
	public function fetchAll($sql,...$args);
	public function fetch($sql,...$args);
	public function fetchColumn($sql,...$args);
	public function execQuick($sql,...$args);
}