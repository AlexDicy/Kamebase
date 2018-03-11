<?php
/**
 * Created by HAlexTM on 11/03/2018 17:49
 */

namespace kamebase\database;


use kamebase\database\schema\Schema;

class DefaultTables {
    public function getTables() {
        $tables = [];

        $table = Schema::create("users");
        $table->column("id")->autoIncrement()->primaryKey();
        $table->column("username", "varchar", 40);
        $table->column("name", "varchar", 40);
        $table->column("lastname", "varchar", 40);
        $table->column("email", "varchar", 40);
        $table->column("password", "varchar", 40);
        $tables[] = $table;
    }
}