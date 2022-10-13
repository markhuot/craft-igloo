<?php

namespace markhuot\igloo\migrations;

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
            'parentId' => $this->integer()->notNull(),
            'fieldId' => $this->integer()->notNull(),
            'slot' => $this->string(64)->notNull(),
            'childId' => $this->integer()->notNull(),
            'sort' => $this->integer()->unsigned()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, Table::COMPONENTS, ['parentId', 'fieldId', 'slot', 'childId', 'sort'], true);
        $this->createIndex(null, Table::COMPONENTS, ['uid'], true);
        $this->addForeignKey(null, Table::COMPONENTS, ['parentId'], \craft\db\Table::ELEMENTS, ['id'], 'CASCADE', null);
        $this->addForeignKey(null, Table::COMPONENTS, ['fieldId'], \craft\db\Table::FIELDS, ['id'], 'CASCADE', null);
        $this->addForeignKey(null, Table::COMPONENTS, ['childId'], \craft\db\Table::ELEMENTS, ['id'], 'CASCADE', null);

        $this->createTable(Table::COMPONENTS_PATHS, [
            'id' => $this->primaryKey(),
            'ancestor' => $this->integer()->notNull(),
            'descendant' => $this->integer()->notNull(),
            'depth' => $this->integer()->unsigned()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, Table::COMPONENTS_PATHS, ['ancestor', 'descendant', 'depth'], true);
        $this->createIndex(null, Table::COMPONENTS_PATHS, ['uid'], true);
        $this->addForeignKey(null, Table::COMPONENTS_PATHS, ['ancestor'], \craft\db\Table::ELEMENTS, ['id'], 'CASCADE', null);
        $this->addForeignKey(null, Table::COMPONENTS_PATHS, ['descendant'], \craft\db\Table::ELEMENTS, ['id'], 'CASCADE', null);

        $this->createTable(Table::CONFIG, [
            'id' => $this->primaryKey(),
            'fieldId' => $this->integer()->unsigned()->notNull(),
            'elementId' => $this->integer()->unsigned(),
            'columns' => $this->integer()->unsigned(),
            'template' => $this->text(),
        ]);

        $this->createIndex(null, Table::CONFIG, ['fieldId', 'elementId'], true);

        $this->createTable(Table::STYLES, [
            'id' => $this->primaryKey(),
            'elementId' => $this->integer()->notNull(),
            'fieldId' => $this->integer(),
            'slot' => $this->string(64)->notNull(),
            'variant' => $this->string(64)->notNull(),
            'styles' => $this->text()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists(Table::COMPONENTS);
        $this->dropTableIfExists(Table::COMPONENTS_PATHS);
        $this->dropTableIfExists(Table::CONFIG);
        $this->dropTableIfExists(Table::STYLES);

        return true;
    }
}
