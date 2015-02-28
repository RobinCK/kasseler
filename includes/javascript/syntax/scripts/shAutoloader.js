function autoLoadSyntax(){if(syntaxStartLoad==false){syntaxStartLoad=true;var a=[],b={};if(window["SyntaxHighlighter"]!==undefined){for(var c in SyntaxHighlighter.brushes){var d=SyntaxHighlighter.brushes[c].aliases;for(var e=0;e<d.length;e++)b[d[e]]=true}}$(".syntaxNotReady").each(function(){var c=this.className.match(/brush:\x20*([^;]+)/i);if(c[1].indexOf(" ")>-1){ar=c[1].split(" ");for(var d=0;d<ar.length;d++)if(!b[ar[d]])a.push(ar[d])}else if(!b[c[1]])a.push(c[1]);var e=this});for(var c=0;c<a.length;c++){$.getScript(aliases[a[c]],function(){loadedSyntaxCount--})}loadedSyntaxCount=a.length}if(loadedSyntaxCount==0){syntaxStartLoad=false;if(SyntaxHighlighter.vars.discoveredBrushes!=null){for(var c in SyntaxHighlighter.brushes){var d=SyntaxHighlighter.brushes[c].aliases;for(var e=0;e<d.length;e++){SyntaxHighlighter.vars.discoveredBrushes[d[e]]=c}}}$(".syntaxNotReady").removeClass("syntaxNotReady").addClass("syntaxReady")}}function pathautoload(a){for(i in a)a[i]=a[i].replace("@","includes/javascript/syntax/scripts/");return a}var syntaxStartLoad=false;var loadedSyntaxCount=0;var aliases={applescript:"@shBrushAppleScript.js",actionscript3:"@shBrushAS3.js",as3:"@shBrushAS3.js",bash:"@shBrushBash.js",shell:"@shBrushBash.js",coldfusion:"@shBrushColdFusion.js",cf:"@shBrushColdFusion.js",cpp:"@shBrushCpp.js",c:"@shBrushCpp.js","c#":"@shBrushCSharp.js","c-sharp":"@shBrushCSharp.js",csharp:"@shBrushCSharp.js",css:"@shBrushCss.js",delphi:"@shBrushDelphi.js",pascal:"@shBrushDelphi.js",diff:"@shBrushDiff.js",pas:"@shBrushDiff.js",patch:"@shBrushDiff.js",erl:"@shBrushErlang.js",erlang:"@shBrushErlang.js",groovy:"@shBrushGroovy.js",java:"@shBrushJava.js",jfx:"@shBrushJavaFX.js",javafx:"@shBrushJavaFX.js",jscript:"@shBrushJScript.js",js:"@shBrushJScript.js",javascript:"@shBrushJScript.js",perl:"@shBrushPerl.js",pl:"@shBrushPerl.js",php:"@shBrushPhp.js",text:"@shBrushPlain.js",plain:"@shBrushPlain.js",py:"@shBrushPython.js",python:"@shBrushPython.js",rails:"@shBrushRuby.js","ruby ":"@shBrushRuby.js",rb:"@shBrushRuby.js",ror:"@shBrushRuby.js",sass:"@shBrushSass.js",scss:"@shBrushSass.js",scala:"@shBrushScala.js",sql:"@shBrushSql.js",vb:"@shBrushVb.js",vbnet:"@shBrushVb.js",code:"@shBrushXml.js",html:"@shBrushXml.js",xslt:"@shBrushXml.js",xhtml:"@shBrushXml.js",xml:"@shBrushXml.js"};aliases=pathautoload(aliases)