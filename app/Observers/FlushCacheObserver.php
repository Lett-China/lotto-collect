<?php

namespace App\Observers;

class FlushCacheObserver
{
    public function saved($that)
    {
        $tagPublic  = $that->rememberCacheTag;
        $key        = $that->getPrimaryKey();
        $tagPrivate = $tagPublic . '@' . $that->$key;
        $that->flushCache($tagPrivate);
        $that->flushCache($tagPublic);
    }
}
