<?php

namespace markhuot\igloo\migrations;

use Craft;
use craft\db\Migration;
use markhuot\igloo\db\Table;

/**
 * Install migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createTable(Table::COMPONENTS, [
            'id' => $this->primaryKey(),
            'elementId' => $this->integer()->notNull(),
            'slot' => $this->string(64)->notNull(),
            'componentId' => $this->integer()->notNull(),
            'lft' => $this->integer()->notNull(),
            'rgt' => $this->integer()->notNull(),
        ]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists(Table::COMPONENTS);

        return true;
    }
}
