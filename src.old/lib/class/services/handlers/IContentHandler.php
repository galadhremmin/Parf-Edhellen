<?php
  namespace services\handlers;
  
  interface IContentHandler {
    function handle(array& $content);
  }
