<?php
  namespace auth;
  
  class AccessRight {
    public const NONE   = 0;
    public const READ   = 1;
    public const CREATE = 2;
    public const MODIFY = 4;
    public const DELETE = 8;
    public const ALL    = 2147483647;
  }
