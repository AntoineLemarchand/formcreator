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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorWizard {

   const MENU_CATALOG      = 1;
   const MENU_LAST_FORMS   = 2;
   const MENU_RESERVATIONS = 3;
   const MENU_FEEDS        = 4;
   const MENU_BOOKMARKS    = 5;
   const MENU_HELP         = 6;
   const MENU_FAQ          = 7;

   public static function header($title) {
      global $CFG_GLPI, $HEADER_LOADED;

      // Print a nice HTML-head for help page
      if ($HEADER_LOADED) {
         return;
      }
      $HEADER_LOADED = true;


      $fcConfig = new PluginFormcreatorConfig();
      $config = $fcConfig->getConfig();

      Html::includeHeader($title, 'helpdesk');

      $body_class = "layout_".$_SESSION['glpilayout'];
      if ((strpos($_SERVER['REQUEST_URI'], "form.php") !== false)
            && isset($_GET['id']) && ($_GET['id'] > 0)) {
         if (!CommonGLPI::isLayoutExcludedPage()) {
            $body_class.= " form";
         } else {
            $body_class = "";
         }
      }

      ob_start();
      Html::displayImpersonateBanner();
      $impersonate_banner = ob_get_clean();
      $formcreator_root = FORMCREATOR_ROOTDOC;

      $makeItem = function ($icon, $title, $url) {
         return <<<HTML
            <li>
               <a href="{$url}" title="{$title}">
                  <i class="fa fa-{$icon}"></i>
                  <span>{$title}</span>
               </a>
            </li>
         HTML;
      };

      
      $faqLink = '';
      if (PluginFormcreatorEntityConfig::getUsedConfig('is_kb_separated', Session::getActiveEntity()) == PluginFormcreatorEntityConfig::CONFIG_KB_DISTINCT
         && Session::haveRight('knowbase', KnowbaseItem::READFAQ)) {
            $faqLink = $makeItem('question', __('Knowledge Base', 'formcreator'), $formcreator_root.'/front/knowbaseitem.php');
      }

      $reservationLink = '';
      if (Session::haveRight("reservation", ReservationItem::RESERVEANITEM)) {
         $reservationLink = $makeItem('calendar-check', __('Book an asset', 'formcreator'), $formcreator_root.'/front/reservationitem.php');
      }

      $rssLink = '';
      if (RSSFeed::canView()) {
         $rssLink = $makeItem('rss', __('Consult feeds', 'formcreator'), $formcreator_root.'/front/wizardfeeds.php');
      }

      $homeLink = $makeItem('home', __('Home', 'formcreator'), $CFG_GLPI['root_doc'].'/front/helpdesk.public.php');

      ob_start();
      self::showHeaderTopContent();
      $header_right = ob_get_clean();
      
      ob_start();
      if ($config['enable_ticket_status_counter'] == 1) {
         self::showTicketSummary();
      }
      $ticketSummary = ob_get_clean();
      $title = __('Home');
      echo <<<HTML
      <body class='$body_class' id='plugin_formcreator_serviceCatalog'>
         <div class="plugin_formcreator_container">
            $impersonate_banner
            <input type="checkbox" id="toggle-nav-menu" />
            <header id="header">
               <div id="header_top">
                  <div id="header_logo">
                     <a href=""><div id="c_logo"></div>
                     $ticketSummary
                     <label for="toggle-nav-menu"><i class="fas fa-bars"></i></label>
                  </div>
                  <div id="header_content">
                     <div class="header-left">
                        <ul>
                           $homeLink
                           $faqLink
                           $reservationLink
                           $rssLink
                        </ul>
                     </div>
                     <div class="header-right">
                        $header_right
                     </div>
                  </div>
               </div>
            </header>
            <main id="page" class="plugin_formcreator_page">
      HTML;

      // call static function callcron() every 5min
      CronTask::callCron();
      Html::displayMessageAfterRedirect();
   }

   public static function footer() {
      return Html::helpFooter();
   }

   public static function showHeaderTopContent() {
      global $CFG_GLPI;

      $fcConfig = new PluginFormcreatorConfig();
      $config = $fcConfig->getConfig();

      // icons
      echo '</ul>';
      echo '<ul class="plugin_formcreator_userMenu_icons">';
      // preferences
      if ($config['enable_profile_info'] == 1) {
         echo '<li id="plugin_formcreator_preferences_icon">';
         echo '<a href="'.$CFG_GLPI["root_doc"].'/front/preference.php" class="fa fa-cog" title="'.
               __s('My settings').'"><span id="preferences_icon" title="'.__s('My settings').'" alt="'.__s('My settings').'" class="button-icon"></span>';
         echo '</a></li>';
      }
      // Logout
      echo '<li id="plugin_formcreator_logoutIcon" ><a href="'.$CFG_GLPI["root_doc"].'/front/logout.php';      /// logout without noAuto login for extauth
      if (isset($_SESSION['glpiextauth']) && $_SESSION['glpiextauth']) {
         echo '?noAUTO=1';
      }
      echo '" class="fa fa-sign-out fa-sign-out-alt" title="'.__s('Logout').'">';
      echo '<span id="logout_icon" title="'.__s('Logout').'" alt="'.__s('Logout').'" class="button-icon"></span></a>';
      echo '</li>';

      echo '</ul>';

      // avatar
      if ($config['enable_profile_info'] == 1) {
         echo '<span id="plugin_formcreator_avatar">';
         $user = new User;
         $user->getFromDB($_SESSION['glpiID']);
         echo '<a href="'.$CFG_GLPI["root_doc"].'/front/preference.php"
                  title="'.formatUserName (0, $_SESSION["glpiname"],
                                             $_SESSION["glpirealname"],
                                             $_SESSION["glpifirstname"], 0, 20).'">
               <img src="'.User::getThumbnailURLForPicture($user->fields['picture']).'"/>
               </a>
               </span>';
      }

      // Profile and entity selection
      echo '<ul class="plugin_formcreator_entityProfile">';
      if (Session::getLoginUserID()) {
         Html::showProfileSelecter($CFG_GLPI["root_doc"]."/front/helpdesk.public.php");
      }
      echo "</ul>";
   }

   public static function showTicketSummary() {
      // show ticket summary
      echo "<span id='formcreator_servicecatalogue_ticket_summary'>";

      $link = PluginFormcreatorIssue::getSearchURL();
      echo "<span class='status status_incoming'>
            <a href='".$link."?".
                     Toolbox::append_params(PluginFormcreatorIssue::getProcessingCriteria(), '&amp;')."'>
            <span class='status_number'><i class='fas fa-spinner fa-spin'></i></span>
            <label class='status_label'>".__('Processing')."</label>
            </a>
            </span>";

      echo "<span class='status status_waiting'>
            <a href='".$link."?".
                     Toolbox::append_params(PluginFormcreatorIssue::getWaitingCriteria(), '&amp;')."'>
            <span class='status_number'><i class='fas fa-spinner fa-spin'></i></span>
            <label class='status_label'>".__('Pending', 'formcreator')."</label>
            </a>
            </span>";

      echo "<span class='status status_validate'>
            <a href='".$link."?".
                     Toolbox::append_params(PluginFormcreatorIssue::getValidateCriteria(), '&amp;')."'>
            <span class='status_number'><i class='fas fa-spinner fa-spin'></i></span>
            <label class='status_label'>".__('To validate', 'formcreator')."</label>
            </a>
            </span>";

      echo "<span class='status status_solved'>
            <a href='".$link."?".
                     Toolbox::append_params(PluginFormcreatorIssue::getSolvedCriteria(), '&amp;')."'>
            <span class='status_number'><i class='fas fa-spinner fa-spin'></i></span>
            <label class='status_label'>".__('Closed', 'formcreator')."</label>
            </a>
            </span>";

      echo '</span>'; // formcreator_servicecatalogue_ticket_summary
   }

   protected static function findActiveMenuItem() {
      if (PluginFormcreatorEntityConfig::getUsedConfig('is_kb_separated', Session::getActiveEntity()) == PluginFormcreatorEntityConfig::CONFIG_KB_DISTINCT) {
         if (strpos($_SERVER['REQUEST_URI'], "formcreator/front/knowbaseitem.php") !== false
            || strpos($_SERVER['REQUEST_URI'], "formcreator/front/knowbaseitem.form.php") !== false) {
            return self::MENU_FAQ;
         }
      }
      if (strpos($_SERVER['REQUEST_URI'], "formcreator/front/wizard.php") !== false
          || strpos($_SERVER['REQUEST_URI'], "formcreator/front/formdisplay.php") !== false
          || strpos($_SERVER['REQUEST_URI'], "formcreator/front/knowbaseitem.form.php") !== false) {
         return self::MENU_CATALOG;
      }
      if (strpos($_SERVER['REQUEST_URI'], "formcreator/front/issue.php") !== false
          || strpos($_SERVER['REQUEST_URI'], "formcreator/front/issue.form.php") !== false) {
         return self::MENU_LAST_FORMS;
      }
      if (strpos($_SERVER['REQUEST_URI'], "formcreator/front/reservationitem.php") !== false) {
         return self::MENU_RESERVATIONS;
      }
      if (strpos($_SERVER['REQUEST_URI'], "formcreator/front/wizardfeeds.php") !== false) {
         return self::MENU_FEEDS;
      }
      return false;
   }
}
