function makeMoziloVars(){FILE_START="@=",FILE_END="=@",moziloPlace="WEBSITE_TITLE",moziloSyntax="bild|fett|ueber1",moziloPluginsActiv="PLUGIN",moziloSmileys="lach"}define("ace/mode/mozilo",["require","exports","module","ace/lib/oop","ace/mode/text","ace/tokenizer","ace/mode/mozilo_highlight_rules","ace/mode/folding/mozilo"],function(e,t,n){var r=e("../lib/oop"),i=e("./text").Mode,s=e("../tokenizer").Tokenizer,o=e("./mozilo_highlight_rules").MoziloHighlightRules,u=e("./folding/mozilo").FoldMode,a=function(){this.$tokenizer=new s((new o).getRules()),this.foldingRules=new u};r.inherits(a,i),function(){}.call(a.prototype),t.Mode=a}),define("ace/mode/mozilo_highlight_rules",["require","exports","module","ace/lib/oop","ace/mode/text_highlight_rules"],function(e,t,n){var r=e("../lib/oop"),i=e("./text_highlight_rules").TextHighlightRules,s=function(){typeof meditorID=="undefined"&&makeMoziloVars();var e="";this.$rules={start:[{token:"text",regex:"\\^."}]},typeof FILE_START=="string"&&typeof FILE_END=="string"&&FILE_START.length>1&&FILE_END.length>1&&(e={token:["mo-files-place","mo-files","mo-files-place"],regex:"("+FILE_START+"){1,1}(.+?)("+FILE_END+"){1,1}"},this.$rules.start.push(e)),typeof moziloPlace=="string"&&moziloPlace.length>1&&(e={token:["paren.lparen","mo-syntax","paren.rparen"],regex:"(\\{{1,1})("+moziloPlace+")(\\}{1,1})"},this.$rules.start.push(e));if(typeof moziloSyntax=="string"){var t="";moziloSyntax.length>1&&(t=moziloSyntax),typeof moziloUserSyntax=="string"&&(moziloUserSyntax.length>1&&t.length>1?t+="|"+moziloUserSyntax:moziloUserSyntax.length>1&&t.length<1&&(t=moziloUserSyntax)),t.length>1&&(e={token:["paren.lparen","mo-syntax","mo-sep"],regex:"(\\[{1,1})("+t+")(\\||\\={1,1})"},this.$rules.start.push(e),e={token:["paren.lparen","mo-syntax","paren.rparen"],regex:"(\\[{1,1})("+t+")(\\]{1,1})"},this.$rules.start.push(e))}typeof moziloPluginsDeactiv=="string"&&moziloPluginsDeactiv.length>1&&(e={token:["paren.lparen","mo-pugin-deact","mo-sep"],regex:"(\\{{1,1})("+moziloPluginsDeactiv+")(\\|{1,1})"},this.$rules.start.push(e),e={token:["paren.lparen.","mo-pugin-deact","paren.rparen"],regex:"(\\{{1,1})("+moziloPluginsDeactiv+")(\\}{1,1})"},this.$rules.start.push(e)),typeof moziloPluginsDeactiv=="string"&&moziloPluginsDeactiv.length>1&&(e={token:["paren.lparen","mo-pugin-place","mo-sep"],regex:"(\\{{1,1})("+moziloPluginsActiv+")(\\|{1,1})"},this.$rules.start.push(e),e={token:["paren.lparen","mo-pugin-place","paren.rparen"],regex:"(\\{{1,1})("+moziloPluginsActiv+")(\\}{1,1})"},this.$rules.start.push(e)),typeof moziloSmileys=="string"&&moziloSmileys.length>1&&(e={token:["paren.lparen","mo-syntax","mo-sep"],regex:"(\\:{1,1})("+moziloSmileys+")(\\:{1,1})"},this.$rules.start.push(e)),e={token:"mo-sep",regex:"\\|"},this.$rules.start.push(e),e={token:"paren.lparen",regex:"[\\[\\{]"},this.$rules.start.push(e),e={token:"paren.rparen",regex:"[\\]\\}]"},this.$rules.start.push(e)};r.inherits(s,i),t.MoziloHighlightRules=s}),define("ace/mode/folding/mozilo",["require","exports","module","ace/lib/oop","ace/range","ace/mode/folding/fold_mode"],function(e,t,n){var r=e("../../lib/oop"),i=e("../../range").Range,s=e("./fold_mode").FoldMode,o=t.FoldMode=function(){};r.inherits(o,s),function(){this.foldingStartMarker=/(\{|\[){1,1}([^\|\=][\w-]*)(\||=){1,1}/,this.foldingStopMarker=/(\]|\})/,this.getFoldWidget=function(e,t,n){if(t!="markbegin")return"";var r=e.getLine(n),i=r.match(this.foldingStartMarker);if(i){var s=i.index,o=this.openingBracketBlock(e,i[1],n,s);if(o){if(o.start.row<o.end.row)return"start";if(e.getWrapLimit()<o.end.column-o.start.column)return"start"}}return""},this.getFoldWidgetRange=function(e,t,n){var r=e.getLine(n),i=e.getRowLength(n),s=r.match(this.foldingStartMarker);if(s){var o=s.index;if(s[1]&&s[2]){var u=this.openingBracketBlock(e,s[1],n,o);return u.start.column+=s[2].length+1,u}}}}.call(o.prototype)})