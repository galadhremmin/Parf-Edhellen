<?php
  include_once '../lib/system.php';

  header('Content-Type: text/plain; charset=utf-8');
  
  $acc = new \data\entities\Account();
  $acc->load('b6bd8a73b763549335a4ec73ea21635cfb9efa98');
  
  echo $acc->toJSON();
