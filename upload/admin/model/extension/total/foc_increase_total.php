<?php

class ModelExtensionTotalFocIncreaseTotal extends Model {

  public function install () {

  }

  public function uninstall () {

  }

  public function getOptionsList ($language_id) {
    $sql = 'SELECT * FROM ' . DB_PREFIX . 'option_description WHERE language_id = ' . (int)$language_id;
    $result = $this->db->query($sql);
    return $result->rows;
  }

  public function getOptionsValuesList ($language_id) {
    $sql = 'SELECT * FROM ' . DB_PREFIX . 'option_value_description WHERE language_id = ' . (int)$language_id;
    $result = $this->db->query($sql);
    return $result->rows;
  }

}