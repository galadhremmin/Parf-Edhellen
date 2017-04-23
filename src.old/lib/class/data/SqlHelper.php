<?php
  namespace data;

  class SqlHelper {
    public static function paginate($sql, $offset = -1, $length = -1) {
      if (! is_numeric($offset)) {
        $offset = -1;
      }

      if (! is_numeric($length)) {
        $length = -1;
      }

      if ($offset > 0 && $offset < $length) {
        $sql .= ' LIMIT '.$offset.', '.$length;
      }

      return $sql;
    }
  }