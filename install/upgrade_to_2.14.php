<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator is a plugin which allows creation of custom forms of
 * easy access.
 * ---------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of Formcreator.
 *
 * Formcreator is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Formcreator. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * @copyright Copyright © 2011 - 2021 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */
class PluginFormcreatorUpgradeTo2_14 {
   /** @var Migration */
   protected $migration;

   /**
    * @param Migration $migration
    */
    public function upgrade(Migration $migration) {
      global $DB;

      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_configs` (
         `id`                int(11) NOT NULL AUTO_INCREMENT,
         `name`              varchar(255) NOT NULL DEFAULT '',
         `value`             text,
         PRIMARY KEY (`id`),

         UNIQUE KEY `name` (`name`)
       ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

      $DB->query($query) or plugin_formcreator_upgrade_error($migration);

      $count = $DB->request('SELECT COUNT(*) AS count FROM glpi_plugin_formcreator_configs WHERE name = "enable_profile_info"');
      $valueExists = iterator_to_array($count)[0]['count'] > 0;
      if (!$valueExists) {
              $migration->insertInTable("glpi_plugin_formcreator_configs", [
                 'name'  => 'enable_profile_info',
                 'value' => '1'
              ]);
      }
      $count = $DB->request('SELECT COUNT(*) AS count FROM glpi_plugin_formcreator_configs WHERE name = "collapse_menu"');
      $valueExists = iterator_to_array($count)[0]['count'] > 0;
      if (!$valueExists) {
              $migration->insertInTable("glpi_plugin_formcreator_configs", [
                 'name'  => 'collapse_menu',
                 'value' => '0'
              ]);
      }
      $count = $DB->request('SELECT COUNT(*) AS count FROM glpi_plugin_formcreator_configs WHERE name = "default_categories_id"');
      $valueExists = iterator_to_array($count)[0]['count'] > 0;
      if (!$valueExists) {
              $migration->insertInTable("glpi_plugin_formcreator_configs", [
                 'name'  => 'default_categories_id',
                 'value' => '0'
              ]);
      }
      $count = $DB->request('SELECT COUNT(*) AS count FROM glpi_plugin_formcreator_configs WHERE name = "see_all"');
      $valueExists = iterator_to_array($count)[0]['count'] > 0;
      if (!$valueExists) {
              $migration->insertInTable("glpi_plugin_formcreator_configs", [
                 'name'  => 'see_all',
                 'value' => '1'
              ]);
      }
      $count = $DB->request('SELECT COUNT(*) AS count FROM glpi_plugin_formcreator_configs WHERE name = "enable_saved_search"');
      $valueExists = iterator_to_array($count)[0]['count'] > 0;
      if (!$valueExists) {
              $migration->insertInTable("glpi_plugin_formcreator_configs", [
                 'name'  => 'enable_saved_search',
                 'value' => '1'
              ]);
      }
   }

}
