<?php

namespace App\Migrations\Rev0;

use Springy\Database\Connection;

class Migration
{
    public function migrate(Connection $connection)
    {
        return true;
    }

    public function rollback(Connection $connection)
    {
        return true;
    }
}
