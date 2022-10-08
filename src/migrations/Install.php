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
            'elementId' => $this->integer()->unsigned()->notNull(),
            'slot' => $this->string(64)->notNull(),
            'componentId' => $this->integer()->unsigned()->notNull(),
            'lft' => $this->integer()->notNull(),
            'rgt' => $this->integer()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(Table::CONFIG, [
            'id' => $this->primaryKey(),
            'fieldId' => $this->integer()->unsigned()->notNull(),
            'elementId' => $this->integer()->unsigned(),
            'columns' => $this->integer()->unsigned(),
            'template' => $this->text(),
        ]);

        $this->createIndex(null, Table::COMPONENTS, ['elementId', 'slot', 'lft', 'rgt'], false);
        $this->createIndex(null, Table::CONFIG, ['fieldId', 'elementId'], true);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists(Table::COMPONENTS);
        $this->dropTableIfExists(Table::CONFIG);

        return true;
    }
}
