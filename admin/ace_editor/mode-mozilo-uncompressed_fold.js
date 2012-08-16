
// ace/mode/folding/fold_mode ace/mode/folding/cstyle
define('ace/mode/mozilo', ['require', 'exports', 'module' , 'ace/lib/oop', 'ace/mode/text', 'ace/tokenizer', 'ace/mode/mozilo_highlight_rules', 'ace/mode/matching_brace_outdent', 'ace/mode/folding/cstyle'], function(require, exports, module) {
"use strict";

var oop = require("../lib/oop");
var TextMode = require("./text").Mode;
var Tokenizer = require("../tokenizer").Tokenizer;
var MoziloHighlightRules = require("./mozilo_highlight_rules").MoziloHighlightRules;
var MatchingBraceOutdent = require("./matching_brace_outdent").MatchingBraceOutdent;
var CStyleFoldMode = require("./folding/cstyle").FoldMode;

var Mode = function() {
    this.$tokenizer = new Tokenizer(new MoziloHighlightRules().getRules());
    this.$outdent = new MatchingBraceOutdent();
    this.foldingRules = new CStyleFoldMode();
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
                token : ["paren.mo-open", "mo-syntax", "paren.mo-close"],
                regex : "(\\{{1})("+moziloPlace+")(\\}{1})"
            },{
                token : ["paren.mo-open", "mo-syntax", "paren.mo-close"],
                regex : "(\\[{1})("+moziloSyntax+"|"+moziloUserSyntax+")(\\]{1})"
            },{
                token : ["paren.mo-open", "mo-syntax", "mo-sep"],
                regex : "(\\[{1})("+moziloSyntax+"|"+moziloUserSyntax+")(\\||\\=)"
            },{
                token : ["paren.mo-open", "mo-pugin-deact", "mo-sep"],
                regex : "(\\{{1})("+moziloPluginsDeactiv+")(\\|{0,1})"
            },{
                token : ["paren.mo-open", "mo-pugin-place", "mo-sep"],
                regex : "(\\{{1})("+moziloPluginsActiv+")(\\|{0,1})"
            },{
                token : ["paren.mo-open", "mo-pugin-place", "mo-sep"],
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

define('ace/mode/folding/cstyle', ['require', 'exports', 'module' , 'ace/lib/oop', 'ace/range', 'ace/mode/folding/fold_mode'], function(require, exports, module) {
"use strict";

var oop = require("../../lib/oop");
var Range = require("../../range").Range;
var BaseFoldMode = require("./fold_mode").FoldMode;

var FoldMode = exports.FoldMode = function() {};

oop.inherits(FoldMode, BaseFoldMode);

(function() {

//    this.foldingStartMarker = /(\{|\[)[^\}\]]*$|^\s*(\/\*)/;
//    this.foldingStopMarker = /^[^\[\{]*(\}|\])|^[\s\*]*(\*\/)/;

// geht so
    this.foldingStartMarker = /(\{SlimBox2|\[)[^\}\]]*$/;
    this.foldingStopMarker = /^[^\[\{]*(\}|\])/;

//    this.foldingStartMarker = /(\{|\[)[^\}\]\{\[]*$/;
//    this.foldingStopMarker = /^[^\[\{\{\[]*(\}|\])/;

//    this.foldingStartMarker = /(\{|\[)*$/;
//    this.foldingStopMarker = /^[^\[\{]*(\}|\])/;
    
    this.getFoldWidgetRange = function(session, foldStyle, row) {
        var line = session.getLine(row);
        var match = line.match(this.foldingStartMarker);
/*
$("#out").html($("#out").html()+"<br />line="+line);
$("#out").html($("#out").html()+"<br />match="+match);
*/
        if (match) {
            var i = match.index;

            if (match[1])
                return this.openingBracketBlock(session, match[1], row, i);

            var range = session.getCommentFoldRange(row, i + match[0].length);
            range.end.column -= 2;
            return range;
        }

        if (foldStyle !== "markbeginend")
            return;
            
        var match = line.match(this.foldingStopMarker);
        if (match) {
            var i = match.index + match[0].length;

            if (match[2]) {
                var range = session.getCommentFoldRange(row, i);
                range.end.column -= 2;
                return range;
            }

            var end = {row: row, column: i};
            var start = session.$findOpeningBracket(match[1], end);
            
            if (!start)
                return;

            start.column++;
            end.column--;

            return  Range.fromPoints(start, end);
        }
    };
    
}).call(FoldMode.prototype);

});

define('ace/mode/folding/fold_mode', ['require', 'exports', 'module' , 'ace/range'], function(require, exports, module) {
"use strict";

var Range = require("../../range").Range;

var FoldMode = exports.FoldMode = function() {};

(function() {

    this.foldingStartMarker = null;
    this.foldingStopMarker = null;

    // must return "" if there's no fold, to enable caching
    this.getFoldWidget = function(session, foldStyle, row) {
        var line = session.getLine(row);
        if (this.foldingStartMarker.test(line))
            return "start";
        if (foldStyle == "markbeginend"
                && this.foldingStopMarker
                && this.foldingStopMarker.test(line))
            return "end";
        return "";
    };
    
    this.getFoldWidgetRange = function(session, foldStyle, row) {
        return null;
    };

    this.indentationBlock = function(session, row, column) {
        var re = /^\s*/;
        var startRow = row;
        var endRow = row;
        var line = session.getLine(row);
        var startColumn = column || line.length;
        var startLevel = line.match(re)[0].length;
        var maxRow = session.getLength()
        
        while (++row < maxRow) {
            line = session.getLine(row);
            var level = line.match(re)[0].length;

            if (level == line.length)
                continue;

            if (level <= startLevel)
                break;

            endRow = row;
        }

        if (endRow > startRow) {
            var endColumn = session.getLine(endRow).length;
            return new Range(startRow, startColumn, endRow, endColumn);
        }
    };

    this.openingBracketBlock = function(session, bracket, row, column) {
        var start = {row: row, column: column + 1};
        var end = session.$findClosingBracket(bracket, start);
        if (!end)
            return;

        var fw = session.foldWidgets[end.row];
        if (fw == null)
            fw = this.getFoldWidget(session, end.row);

        if (fw == "start") {
            end.row --;
            end.column = session.getLine(end.row).length;
        }
        return Range.fromPoints(start, end);
    };

}).call(FoldMode.prototype);

});
