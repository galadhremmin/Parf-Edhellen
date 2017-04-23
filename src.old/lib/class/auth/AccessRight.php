<?php
  namespace auth;
  
  class AccessRight {
    const NONE   = 0;
    const READ   = 1;
    const CREATE = 2;
    const MODIFY = 4;
    const DELETE = 8;
    const ALL    = 2147483647;
  }
