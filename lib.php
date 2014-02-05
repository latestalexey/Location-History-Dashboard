<?php
class LHD {
	private $connection;

	private $data;

	private $placeholders;

	public function __construct() {
		$this->data = array();
		$this->placeholders = array();
		$cfg = parse_ini_file('config.ini', true);
		$this->connection = new PDO($cfg['connection']['dns'], $cfg['connection']['user'], $cfg['connection']['pass']);
	}

	public function add($object) {
		if (!$this->exists($object)) {
			$date = new DateTime();
			$date->setTimestamp($object->timestampMs/1000);
			$this->placeholders[] = "(?,?,?,?,?)";
			$this->data[] = $object->timestampMs;
			$this->data[] = $object->latitudeE7;
			$this->data[] = $object->longitudeE7;
			$this->data[] = $object->accuracy;
			$this->data[] = $date->format('Y-m-d H:i:s');
		}
	}

	public function commit() {
		$sql  = "INSERT INTO lhd (timestampMs, latitude, longitude, accuracy, pointdate) VALUES "; 
		$sql .= implode(', ', $this->placeholders);
		$query = $this->connection->prepare($sql);
		$b = $query->execute($this->data);
		if (!$b) {
			var_dump($query->errorInfo());
			var_dump($sql);
			var_dump($this->data);
		}

		$this->data = array();
		$this->placeholders = array();
	}

	public function exists($object) {
		$query = $this->connection->prepare("SELECT timestampMs FROM lhd WHERE timestampMs = :ts");
		$query->execute(array('ts' => $object->timestampMs));

		return $query->fetch(PDO::FETCH_ASSOC);
	}
}