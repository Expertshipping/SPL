<?php

namespace ExpertShipping\Spl\Enum;

enum AlertOriginEnum:string {
    case ALL = 'ALL';
    case B2B = 'B2B';
    case RETAIL = 'RETAIL';
    case RETAIL_RESELLER = 'RETAIL_RESELLER';
}
