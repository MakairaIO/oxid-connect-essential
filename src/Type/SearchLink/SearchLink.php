<?php

/**
 * This file is part of a marmalade GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 * Version:    1.0
 * Author:     Jens Richter <richter@marmalade.de>
 * Author URI: http://www.marmalade.de
 */

namespace Makaira\OxidConnectEssential\Type\SearchLink;

use Makaira\OxidConnectEssential\Type;

class SearchLink extends Type
{
    public string $MARM_OXSEARCH_SEARCHLINKS_EXTERNAL;
    public string $MARM_OXSEARCH_SEARCHLINKS_KEYWORDS;
    public string $MARM_OXSEARCH_SEARCHLINKS_SITE;
    public string $MARM_OXSEARCH_SEARCHLINKS_TITLE;
    public string $OXSHOPID;
}
