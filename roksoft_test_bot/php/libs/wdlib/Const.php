<?php

namespace WDLIB;

define("UINT32_MAX", 0xffffffff);

const OK = 0;
const ERROR = -1;
const ERROR_DB = -2;
const ERROR_NOT_FOUND = -3;
const ERROR_ACCESS_DENIED = -4;
const ERROR_AUTH = -5;
const ERROR_INVALID_DATA = -6;
const ERROR_NOT_ENOUGH = -7;
const ERROR_ALREADY = -9;
const ERROR_ABUSE = -10;
const ERROR_FLOOD = -11;
const ERROR_BANNED = -12;
const ERROR_BUSY = -13;
const ERROR_LIMIT = -14;
const ERROR_TOTAL_LIMIT = -15;
const ERROR_API = -16;
const ERROR_REDIRECT = -17;

// SORTING
const SORT_DEFAULT = 0;
const SORT_DESC = -1;
const SORT_ASC = 1;

// OUTPUT
const OUTPUT_HTML = 0;
const OUTPUT_JSON = 1;
const OUTPUT_RAW = 2;

// GENDER
const FEMALE = 1;
const MALE = 2;

// SOCIAL NETWORK APIs
const API_LOCAL = 0;
// mamba
const API_MAMBA = 1;
// vkontakte
const API_VK = 2;
// fotostrana
const API_FS = 3;
// odnoklassniki
const API_OK = 7;
// moy mir
const API_MM = 9;
// AUTOBOT
const API_AUTOBOT = 11;
const API_VK_AUTH = 19;
const API_OK_AUTH = 20;
const API_TELEGRAM = 21;
// STANDALONE WEBSITE -> for AUTH
const API_STANDALONE = 99;
// TOTAL for summary stats
const API_TOTAL = 100;
