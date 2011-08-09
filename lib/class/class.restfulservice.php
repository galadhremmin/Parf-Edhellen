<?php
  abstract class RESTfulService {
    public abstract function handleRequest(&$data);
    public abstract function handleParameterizedRequest(&$data, $param = null);
  }
?>