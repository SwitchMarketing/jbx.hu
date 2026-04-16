# Product Database Normalization Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Move from a single JSON-heavy `products` table to a normalized relational structure (Master-Variant model) to support local management, easy editing, and efficient querying of product variations.

**Architecture:** We will implement a Master-Variant pattern where shared data (name, category, description) lives in a `product_masters` table, and SKU-specific data (price, stock, attributes) lives in `product_variants`. Dynamic characteristics (size, color, etc.) will be stored in normalized attribute tables.

**Tech Stack:** CI4 (PHP), MariaDB, Sencha ExtJS.

---

### Task 1: Create Normalized Tables (Migrations)

**Files:**
- Create: `app/Database/Migrations/2026-04-16-110000_CreateNormalizedProductTables.php`

- [ ] **Step 1: Create the migration file**

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNormalizedProductTables extends Migration
{
    public function up()
    {
        // 1. Master Products
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'category_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => '255'],
            'slug'        => ['type' => 'VARCHAR', 'constraint' => '255'],
            'unit'        => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'description' => ['type' => 'TEXT', 'null' => true],
            'state'       => ['type' => 'VARCHAR', 'constraint' => '50', 'default' => 'live'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('category_id');
        $this->forge->createTable('product_masters');

        // 2. Product Variants
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'master_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'unas_id'    => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'sku'        => ['type' => 'VARCHAR', 'constraint' => '100'],
            'price'      => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'stock'      => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'state'      => ['type' => 'VARCHAR', 'constraint' => '50', 'default' => 'live'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('master_id', 'product_masters', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('sku');
        $this->forge->createTable('product_variants');

        // 3. Attributes
        $this->forge->addField([
            'id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => '100'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('attributes');

        // 4. Variant Attribute Values
        $this->forge->addField([
            'variant_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'attribute_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'value'        => ['type' => 'VARCHAR', 'constraint' => '255'],
        ]);
        $this->forge->addKey(['variant_id', 'attribute_id'], true);
        $this->forge->addForeignKey('variant_id', 'product_variants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('attribute_id', 'attributes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('variant_attribute_values');
    }

    public function down()
    {
        $this->forge->dropTable('variant_attribute_values');
        $this->forge->dropTable('attributes');
        $this->forge->dropTable('product_variants');
        $this->forge->dropTable('product_masters');
    }
}
```

- [ ] **Step 2: Run the migration**

Run: `php spark migrate`
Expected: SUCCESS

---

### Task 2: Data Normalization Script

**Files:**
- Create: `app/Commands/NormalizeData.php`

- [ ] **Step 1: Create the normalization command**

(See script in Task 2 summary above)

- [ ] **Step 2: Run the command**

Run: `php spark db:normalize-products`
Expected: SUCCESS

---

### Task 3: Models and Backend Refactor

**Files:**
- Create: `app/Models/ProductMasterModel.php`
- Create: `app/Models/ProductVariantModel.php`
- Modify: `app/Controllers/Admin/Products.php`

- [ ] **Step 1: Create `ProductMasterModel.php`** (Extends `BaseModel`, table `product_masters`)
- [ ] **Step 2: Create `ProductVariantModel.php`** (Table `product_variants`)
- [ ] **Step 3: Update `Admin\Products.php`** to use `ProductMasterModel` as main model and include variants in `show`.

---

### Task 4: Admin UI Refactor

**Files:**
- Modify: `public/admin/app/view/products/Products.js`
- Modify: `public/admin/app/view/products/ProductsController.js`

- [ ] **Step 1: Update grid to use `ProductMasterModel`**
- [ ] **Step 2: Create a sub-grid or detail view for `product_variants` in the editor**
- [ ] **Step 3: Rebuild Admin**

Run: `cd public/admin && sencha app build production`
