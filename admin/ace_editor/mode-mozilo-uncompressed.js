
// ace/mode/folding/fold_mode ace/mode/folding/cstyle
define('ace/mode/mozilo', ['require', 'exports', 'module' , 'ace/lib/oop', 'ace/mode/text', 'ace/tokenizer', 'ace/mode/mozilo_highlight_rules', 'ace/mode/matching_brace_outdent'], function(require, exports, module) {
"use strict";

var oop = require("../lib/oop");
var TextMode = require("./text").Mode;
var Tokenizer = require("../tokenizer").Tokenizer;
var MoziloHighlightRules = require("./mozilo_highlight_rules").MoziloHighlightRules;
var MatchingBraceOutdent = require("./matching_brace_outdent").MatchingBraceOutdent;

var Mode = function() {
    this.$tokenizer = new Tokenizer(new MoziloHighlightRules().getRules());
    this.$outdent = new MatchingBraceOutdent();
};
oop.inherits(Mode, TextMode);

(function() {
    this.getNextLineIndent = function(state, line, tab) {
        if (state == "intag")
            return tab;
        return "";
    };

    this.checkOutdent = function(state, line, input) {
        return this.$outdent.checkOutdent(line, input);
    };

    this.autoOutdent = function(state, doc, row) {
        this.$outdent.autoOutdent(doc, row);
    };
    
}).call(Mode.prototype);

exports.Mode = Mode;

});


define('ace/mode/mozilo_highlight_rules', ['require', 'exports', 'module' , 'ace/lib/oop', 'ace/mode/text_highlight_rules'], function(require, exports, module) {
"use strict";

var oop = require("../lib/oop");
var TextHighlightRules = require("./text_highlight_rules").TextHighlightRules;

var MoziloHighlightRules = function() {
    this.$rules = {
        "start" : [
            {
                token : "text",
                regex : "\\^."
            },{
                token : ["mo-files-place", "mo-files", "mo-files-place"],
                regex : "("+FILE_START+"){1}(.+?)("+FILE_END+"){1}"
            },{
                token : ["paren.mo-open", "mo-syntax", "paren.mo-close"],
                regex : "(\\{{1})("+moziloPlace+")(\\}{1})"
            },{
                token : ["paren.mo-open", "mo-syntax", "paren.mo-close"],
                regex : "(\\[{1})("+moziloSyntax+"|"+moziloUserSyntax+")(\\]{1})"
            },{
                token : ["paren.mo-open", "mo-syntax", "mo-sep"],
                regex : "(\\[{1})("+moziloSyntax+"|"+moziloUserSyntax+")(\\||\\={1})"
            },{
                token : ["paren.mo-open", "mo-pugin-deact", "paren.mo-close"],
                regex : "(\\{{1})("+moziloPluginsDeactiv+")(\\}{1})"
            },{
                token : ["paren.mo-open", "mo-pugin-place", "paren.mo-close"],
                regex : "(\\{{1})("+moziloPluginsActiv+")(\\}{1})"
            },{
                token : ["paren.mo-open", "mo-pugin-deact", "mo-sep"],
                regex : "(\\{{1})("+moziloPluginsDeactiv+")(\\|{1})"
            },{
                token : ["paren.mo-open", "mo-pugin-place", "mo-sep"],
                regex : "(\\{{1})("+moziloPluginsActiv+")(\\|{1})"
            },{
                token : ["paren.mo-open", "mo-syntax", "mo-sep"],
                regex : "(\\:{1})("+moziloSmileys+")(\\:{1})"
            },{
                token : "mo-sep",
                regex : "\\|"
            },{
                token : "paren.mo-open",
                regex : "\\[|\\{"
            },{
                token : "paren.mo-close",
                regex : "\\]|\\}"
            }
        ],
    };
};

oop.inherits(MoziloHighlightRules, TextHighlightRules);

exports.MoziloHighlightRules = MoziloHighlightRules;

});


define('ace/mode/matching_brace_outdent', ['require', 'exports', 'module' , 'ace/range'], function(require, exports, module) {
"use strict";

var Range = require("../range").Range;

var MatchingBraceOutdent = function() {};

(function() {

    this.checkOutdent = function(line, input) {
        if (! /^\s+$/.test(line))
            return false;

        return /^\s*\}/.test(input);
    };

    this.autoOutdent = function(doc, row) {
        var line = doc.getLine(row);
        var match = line.match(/^(\s*\})/);

        if (!match) return 0;

        var column = match[1].length;
        var openBracePos = doc.findMatchingBracket({row: row, column: column});

        if (!openBracePos || openBracePos.row == row) return 0;

        var indent = this.$getIndent(doc.getLine(openBracePos.row));
        doc.replace(new Range(row, 0, row, column-1), indent);
    };

    this.$getIndent = function(line) {
        var match = line.match(/^(\s+)/);
        if (match) {
            return match[1];
        }

        return "";
    };

}).call(MatchingBraceOutdent.prototype);

exports.MatchingBraceOutdent = MatchingBraceOutdent;
});
