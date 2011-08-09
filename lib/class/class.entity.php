<?php
  abstract class Entity {
    public abstract function validate();
    public abstract function load($numericId);
    public abstract function save();
  }
?>