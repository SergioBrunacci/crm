<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_25_8;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateIndexes implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_sales_opportunity');
        $indexName    = 'opportunities_by_status_idx';
        $indexColumns = [
            'organization_id',
            'status_id',
            'close_revenue',
            'budget_amount',
            'created_at'
        ];
        if ($table->hasIndex($indexName) && $table->getIndex($indexName)->getColumns() !== $indexColumns) {
            $table->dropIndex($indexName);
            $table->addIndex($indexColumns, $indexName);
        } else {
            $table->addIndex($indexColumns, $indexName);
        }
    }
}
