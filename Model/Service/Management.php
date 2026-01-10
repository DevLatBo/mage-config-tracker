<?php

namespace Devlat\Settings\Model\Service;

use Magento\Framework\App\ResourceConnection;

class Management
{

    /** @var string */
    const TABLE = "devlat_settings_tracker";
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * Constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Removes the setting tracker table from the database.
     * @return void
     */
    public function dropTable(): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('devlat_settings_tracker');

        if($connection->isTableExists($tableName)){
            $connection->dropTable($tableName);
        }
    }
}
