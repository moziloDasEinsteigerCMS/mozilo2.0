/* ***** BEGIN LICENSE BLOCK *****
 * Version: MPL 1.1/GPL 2.0/LGPL 2.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is Ajax.org Code Editor (ACE).
 *
 * The Initial Developer of the Original Code is
 * Ajax.org B.V.
 * Portions created by the Initial Developer are Copyright (C) 2010
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *      Fabian Jakobs <fabian AT ajax DOT org>
 *
 * Alternatively, the contents of this file may be used under the terms of
 * either the GNU General Public License Version 2 or later (the "GPL"), or
 * the GNU Lesser General Public License Version 2.1 or later (the "LGPL"),
 * in which case the provisions of the GPL or the LGPL are applicable instead
 * of those above. If you wish to allow use of your version of this file only
 * under the terms of either the GPL or the LGPL, and not to allow others to
 * use your version of this file under the terms of the MPL, indicate your
 * decision by deleting the provisions above and replace them with the notice
 * and other provisions required by the GPL or the LGPL. If you do not delete
 * the provisions above, a recipient may use your version of this file under
 * the terms of any one of the MPL, the GPL or the LGPL.
 *
 * ***** END LICENSE BLOCK ***** */

define('ace/theme/mozilo', ['require', 'exports', 'module' , 'ace/lib/dom'], function(require, exports, module) {

exports.cssClass = "ace-mozilo";
exports.cssText = ".ace-mozilo .ace_editor {\
  border: 2px solid rgb(159, 159, 159);\
}\
\
.ace-mozilo .ace_editor.ace_focus {\
  border: 2px solid #327fbd;\
}\
\
.ace-mozilo .ace_gutter {\
  border-right: 1px solid silver;\
  background: #F8F8F8;\
  color: #333;\
  overflow : hidden;\
}\
\
.ace-mozilo .ace_gutter-layer {\
  width: 100%;\
  text-align: right;\
}\
\
.ace-mozilo .ace_print_margin {\
  width: 1px;\
  background: #e8e8e8;\
}\
\
.ace-mozilo .ace_text-layer {\
  cursor: text;\
}\
\
.ace-mozilo .ace_cursor {\
  border-left: 2px solid black;\
}\
\
.ace-mozilo .ace_cursor.ace_overwrite {\
  border-left: 0px;\
  border-bottom: 1px solid black;\
}\
\
.ace-mozilo .ace_line .ace_invisible {\
  color: rgb(191, 191, 191);\
}\
\
.ace-mozilo .ace_line .ace_constant.ace_buildin {\
  color: rgb(88, 72, 246);\
}\
\
.ace-mozilo .ace_line .ace_constant.ace_language {\
  color: rgb(88, 92, 246);\
}\
\
.ace-mozilo .ace_line .ace_constant.ace_library {\
  color: rgb(6, 150, 14);\
}\
\
.ace-mozilo .ace_line .ace_invalid {\
  background-color: rgb(153, 0, 0);\
  color: white;\
}\
\
.ace-mozilo .ace_line .ace_fold {\
}\
\
.ace-mozilo .ace_line .ace_support.ace_function {\
  color: rgb(60, 76, 114);\
}\
\
.ace-mozilo .ace_line .ace_support.ace_constant {\
  color: rgb(6, 150, 14);\
}\
\
.ace-mozilo .ace_line .ace_support.ace_type,\
.ace-mozilo .ace_line .ace_support.ace_class {\
  color: rgb(109, 121, 222);\
}\
\
.ace-mozilo .ace_variable.ace_parameter {\
  font-style:italic;\
color:#FD971F;\
}\
.ace-mozilo .ace_line .ace_keyword.ace_operator {\
  color: rgb(104, 118, 135);\
}\
\
.ace-mozilo .ace_line .ace_comment {\
  color: #236e24;\
}\
\
.ace-mozilo .ace_line .ace_comment.ace_doc {\
  color: #236e24;\
}\
\
.ace-mozilo .ace_line .ace_comment.ace_doc.ace_tag {\
  color: #236e24;\
}\
\
.ace-mozilo .ace_line .ace_constant.ace_numeric {\
  color: rgb(0, 0, 205);\
}\
\
.ace-mozilo .ace_line .ace_mo-syntax {\
  color: rgb(14, 0, 179);\
}\
\
.ace-mozilo .ace_line .ace_mo-pugin-place {\
  color: rgb(170, 0, 179);\
}\
\
.ace-mozilo .ace_line .ace_mo-pugin-deact {\
  text-decoration:line-through;\
  color: rgb(170, 0, 179);\
}\
\
.ace-mozilo .ace_line .ace_mo-files {\
  color: rgb(0, 197, 0);\
}\
\
.ace-mozilo .ace_line .ace_mo-files-place {\
  color: rgb(120, 120, 255);\
}\
\
.ace-mozilo .ace_line .ace_mo-open {\
  font-weight:bold;\
  color: rgb(179, 0, 0);\
}\
\
.ace-mozilo .ace_line .ace_mo-close {\
  font-weight:bold;\
  color: rgb(179, 0, 0);\
}\
\
.ace-mozilo .ace_line .ace_mo-sep {\
  font-weight:bold;\
  color: rgb(179, 0, 0);\
}\
\
.ace-mozilo .ace_line .ace_mo-is {\
  font-weight:bold;\
  color: rgb(179, 0, 0);\
}\
\
.ace-mozilo .ace_line .ace_variable {\
  color: rgb(49, 132, 149);\
}\
\
.ace-mozilo .ace_line .ace_xml_pe {\
  color: rgb(104, 104, 91);\
}\
\
.ace-mozilo .ace_entity.ace_name.ace_function {\
  color: #0000A2;\
}\
\
.ace-mozilo .ace_markup.ace_markupine {\
    text-decoration:underline;\
}\
\
.ace-mozilo .ace_markup.ace_heading {\
  color: rgb(12, 7, 255);\
}\
\
.ace-mozilo .ace_markup.ace_list {\
  color:rgb(185, 6, 144);\
}\
\
.ace-mozilo .ace_marker-layer .ace_selection {\
  background: rgb(181, 213, 255);\
}\
\
.ace-mozilo .ace_marker-layer .ace_step {\
  background: rgb(252, 255, 0);\
}\
\
.ace-mozilo .ace_marker-layer .ace_stack {\
  background: rgb(164, 229, 101);\
}\
\
\/*zugeörige klammer*/\
.ace-mozilo .ace_marker-layer .ace_bracket {\
  background: rgba(255, 171, 68, 0.5);\
  margin: -1px 0 0 -1px;\
  border: 1px solid rgb(192, 192, 192);\
}\
\
.ace-mozilo .ace_marker-layer .ace_active_line {\
  background: rgba(0, 0, 0, 0.04);\
}\
\
.ace-mozilo .ace_marker-layer .ace_selected_word {\
  background: rgb(250, 250, 255);\
  border: 1px solid rgb(200, 200, 250);\
}\
\
.ace-mozilo .ace_storage,\
.ace-mozilo .ace_line .ace_keyword,\
.ace-mozilo .ace_meta.ace_tag {\
  color: rgb(147, 15, 128);\
}\
\
.ace-mozilo .ace_string.ace_regex {\
  color: rgb(255, 0, 0)\
}\
\
.ace-mozilo .ace_line .ace_string,\
.ace-mozilo .ace_entity.ace_other.ace_attribute-name{\
  color: #994409;\
}";

var dom = require("../lib/dom");
dom.importCssString(exports.cssText, exports.cssClass);

});
