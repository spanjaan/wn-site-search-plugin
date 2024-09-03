<?php

namespace SpAnjaan\Sitesearch\Models;

use Winter\Storm\Database\Model;

/**
 * QueryLogExport Model
 */
class QueryLogExport extends \Backend\Models\ExportModel
{
    public function exportData($columns, $sessionKey = null)
    {
        $subscribers = QueryLog::all();
        $subscribers->each(function ($subscriber) use ($columns) {
            $subscriber->addVisible($columns);
        });

        return $subscribers->toArray();
    }
}

