<?php

/**
 * Iterator for CSV, TSV or other xSV data files
 *
 * @author murchik <mixturchik@gmail.com>
 * @version 0.0.1
 */
class CharSeparatedValues implements Iterator {

	private $_delimiter = "\t";
	private $_enclosure = '"';
	private $_escape = '\\';

	private $_file = '';
	private $_handler = null;
	private $_headersAtFirstRow = false;

	private $_fields = array();

	private $_data = array();
	private $_fileLineNumber = 0;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param String $file path to file to work with
	 * @param Bool $headersAtFirstRow is the first row of file a set of headers
	 * @param String $delimiter data delimiter
	 * @param String $enclosure enclosure character
	 * @param String $escape escape special chars character
	 * @return void
	 */
	public function __construct($file, $headersAtFirstRow = null,
		$delimiter = null, $enclosure = null, $escape = null) {

		$this->_file = (string) $file;

		if (isset($headersAtFirstRow)) {
			$this->_headersAtFirstRow = (bool) $headersAtFirstRow;
		}

		if (isset($delimiter)) {
			$this->_delimiter = (string) $delimiter;
		}

		if (isset($enclosure)) {
			$this->_enclosure = (string) $enclosure;
		}

		if (isset($escape)) {
			$this->_escape = (string) $escape;
		}
	}

	/**
	 * Data getter
	 *
	 * @access public
	 * @param String $name data key
	 * @return Mixed
	 */
	public function __get($name) {
		$field = array_search($name, $this->_fields);
		if ($field !== false && isset($this->_data[$field])) {
			return $this->_data[$field];
		}
	}

	/**
	 * Get field list
	 *
	 * @access public
	 * @return Array
	 */
	public function getFieldList() {
		$this->_loadFile();
		return $this->_fields;
	}

	/**
	 * Add data to file
	 *
	 * @access public
	 * @return void
	 */
	public function add($data) {
		$handler = fopen($this->_file, 'a');

		if (!$handler) {
			throw Exception("Can't load file " . $file);
		}

		if (flock($handler, LOCK_EX))
		{
			fputcsv($handler, implode($this->_delimiter, $data),
				$this->_delimiter,
				$this->_enclosure);
			flock($this->_handler, LOCK_UN);
		}
		fclose($f);
	}

	/**
	 * Iterator::rewind() implementation
	 */
	public function rewind() {
		$this->_loadFile();
	}
	/**
	 * Iterator::current() implementation
	 */
	public function current()
	{
		return $this->_data;
	}

	/**
	 * Iterator::key() implementation
	 */
	public function key()
	{
		return $this->_fileLineNumber;
	}

	/**
	 * Iterator::next() implementation
	 */
	public function next()
	{
		$this->_data = $this->_fgetcsvWrapper($this->_handler);
		$this->_fileLineNumber++;
	}

	/**
	 * Iterator::valid() implementation
	 */
	public function valid()
	{
		return $this->_data ? true : false;
	}

	/**
	 * Load data file
	 *
	 * @access private
	 * @return void
	 */
	private function _loadFile() {
		$this->_handler = fopen($this->_file, 'r');

		if (!$this->_handler) {
			throw Exception("Can't load file " . $file);
		}

		if ($this->_headersAtFirstRow) {
			$this->_fields = $this->_fgetcsvWrapper($this->_handler);
		}

		$this->_data = $this->_fgetcsvWrapper($this->_handler);
	}

	/**
	 * Get array of values, move pointer to next line
	 *
	 * @access private
	 * @param Resource $handler file resource handler
	 * @return array
	 */
	private function _fgetcsvWrapper($handler) {
		return fgetcsv($handler, 0, $this->_delimiter, $this->_enclosure, $this->_escape);
	}
}
