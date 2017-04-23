<?php
namespace controllers;

class DonationsController extends Controller {
  public function __construct(\TemplateEngine $engine) {
    parent::__construct('Donations', $engine, false);
  }
}

