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
		$date = new DateTime();
		$date->setTimestamp($object->timestampMs/1000);
		$this->placeholders[] = "(?,?,?,?,?)";
		$this->data[] = $object->timestampMs;
		$this->data[] = $object->latitudeE7;
		$this->data[] = $object->longitudeE7;
		$this->data[] = (property_exists($object, 'accuracy')) ? $object->accuracy : 0;
		$this->data[] = $date->format('Y-m-d H:i:s');
	}

	public function commit() {
		$sql  = "INSERT INTO lhd (timestampMs, latitude, longitude, accuracy, pointdate) VALUES "; 
		$sql .= implode(', ', $this->placeholders);
		$sql .= " ON DUPLICATE KEY UPDATE timestampMs=VALUES(timestampMs), latitude=VALUES(latitude), longitude=VALUES(longitude), accuracy=VALUES(accuracy), pointdate=VALUES(pointdate)";
		$query = $this->connection->prepare($sql);
		$b = $query->execute($this->data);
		if (!$b) {
			var_dump($_SESSION['debug']);
			var_dump($query->errorInfo());
			var_dump($sql);
			var_dump($this->data);
		}

		$this->data = array();
		$this->placeholders = array();
	}
}