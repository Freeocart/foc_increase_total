<?php

class ModelExtensionTotalFocIncreaseTotal extends Model {

  public function install () {

  }

  public function uninstall () {

  }

  public function getOptionsList ($language_id) {
    $sql = 'SELECT option_id,name FROM ' . DB_PREFIX . 'option_description WHERE language_id = ' . (int)$language_id;
    $result = $this->db->query($sql);
    return $result->rows;
  }

  public function getOptionsValuesList ($language_id) {
    $sql = 'SELECT option_value_id,option_id,name FROM ' . DB_PREFIX . 'option_value_description WHERE language_id = ' . (int)$language_id;
    $result = $this->db->query($sql);
    return $result->rows;
  }

  public function getAttributesList ($language_id) {
    $sql = 'SELECT `a`.attribute_id,`a`.attribute_group_id,`ad`.`name` FROM ' . DB_PREFIX . 'attribute_description AS `ad` LEFT JOIN ' . DB_PREFIX .'attribute AS `a` ON `a`.attribute_id = `ad`.attribute_id WHERE language_id = ' . (int)$language_id;
    $result = $this->db->query($sql);
    return $result->rows;
  }

  public function getAttributeGroupList ($language_id) {
    $sql = 'SELECT attribute_group_id, name FROM ' . DB_PREFIX . 'attribute_group_description WHERE language_id = ' . (int)$language_id;
    $result = $this->db->query($sql);
    return $result->rows;
  }

}