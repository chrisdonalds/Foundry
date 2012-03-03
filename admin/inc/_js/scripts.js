/**
* --------------------------------------------------------------------------------------
* Scripts
*
* - Basic Javascript; not enhanced or extended by any library
* --------------------------------------------------------------------------------------
*/

var IE = false;
if(navigator.appName == "Microsoft Internet Explorer") IE = true;

/* CKEditor Helpers */

function ckeditor_autosave(elem, textbox) {
    var textout = jQuery('#textbox');
    var editordata = elem.getData();
    textout.value = editordata;
}

/* Generic */

function addDate(datefield, targetfield, days){
    var e = jQuery('#'+datefield);
    var d1 = e.value;
    /* convert yyyy-mm-dd to mm-dd-yyyy */
    var d1y = d1.substring(0, 4);
    var d1m = d1.substring(5, 7);
    var d1d = d1.substring(8, 10);
    d1 = d1y + "/" + d1m + "/" + d1d;

    /* add number of days */
    var d = new Date(d1);
    d.setDate(d.getDate() + days);

    /* change target field value */
    var t = jQuery('#'+targetfield);
    t.value = ((d.getYear()+1900)+"-"+(d.getMonth()+1)+"-"+d.getDate());
}

function convertTime(timedata){
    if(timedata >= "0000" && timedata <= "2400"){
        var newhour = parseInt(timedata.substring(0, 1));
        var newmin  = parseInt(timedata.substring(2, 3));
        var meridian = " am";
        if (newhour > 12) {
            meridian = "pm";
            newhour -= 12;
        }
        var newtime = newhour+":"+newmin+" "+meridian;
    }
    return newtime;
}

/* Prototypes */

String.prototype.stripHTML = function(){
    var matchTag = /<(?:.|\s)*?>/g;
    return this.replace(matchTag, "");
};

/* Cookies */

function setCookie(c_name, value, expiredays){
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + expiredays);
    document.cookie = c_name+ "=" + escape(value) + ((expiredays == null) ? "" : ";expires="+exdate.toUTCString());
}

function getCookie(c_name){
    if (document.cookie.length > 0){
        c_start=document.cookie.indexOf(c_name + "=");
        if (c_start != -1){
            c_start = c_start + c_name.length+1;
            c_end = document.cookie.indexOf(";", c_start);
            if (c_end == -1) c_end = document.cookie.length;
            return unescape(document.cookie.substring(c_start, c_end));
        }
    }
    return "";
}

/* Email Hide Script */

function parse_email(user, server, domain, subject, body) {
	if(user && server && domain){
		emailto = "mailto:"+user+'@'+server+'.'+domain+"?subject="+subject+"&body="+body;
		window.location = emailto;
	}
}

function submit_edit_form(){
	jQuery('#edit_form').submit();
}

function removeHTMLTags(textfld, isckeditor, limit){
    var strInputCode;
    if(textfld == "") return null;

    if(!isckeditor){
        if(document.getElementById && document.getElementById(textfld)){
            strInputCode = document.getElementById(textfld).value;
        }else{
            strInputCode = textfld;
        }
    }else{
        if(document.getElementById){
            strInputCode = textfld.getData();
        }
    }

    /*
        This line is optional, it replaces escaped brackets with real ones,
        i.e. &lt; is replaced with < and &gt; is replaced with >
    */
    strInputCode = strInputCode.replace(/&(lt|gt);/g, function (strMatch, p1){
        return (p1 == "lt")? "<" : ">";
    });
    //var strTagStrippedText = strInputCode.replace(/<\/?[^>]+(>|$)/g, "");
    var strTagStrippedText = strInputCode.replace(/(<[a-z0-9]+?>)|(\t)/gi, "");
    strTagStrippedText = strTagStrippedText.replace(/^\s+|\s+$/gi, " ");
    strTagStrippedText = strInputCode.replace(/(\n|\r|\n\r|\t){2,}/gi, "");
    if(parseInt(limit) > 0) strTagStrippedText = strTagStrippedText.substring(0, limit);
    return strTagStrippedText;
}

function countChars(textfld, countfld, countwords, limitchars){
    if(textfld == "" || textfld == null || countfld == "" || countfld == null) return false;

    var countobj = jQuery('#'+countfld);
    var isckeditor = false;
    var textobj;

    if(typeof textfld == "string"){
        textobj = jQuery('#'+textfld).val();
    }else {
        textobj = textfld.getData();
        textobj = removeHTMLTags(textobj, false);
        isckeditor = true;
    }

    if(countobj && typeof textobj == "string"){
        if(limitchars > 0 && limitchars != null && textobj.length > limitchars){
            textobj = textobj.substring(0, limitchars);
            if(!isckeditor){
                jQuery('#'+textfld).val(textobj);
            }
        }
        if(countwords){
            countobj.value = textobj.split(' ').length;
        }else{
            var countnum = textobj.length;
            if(isckeditor) countnum -= 8;
            countobj.value = countnum;
        }
    }
}

/* String Inflectors (pluralize, singularize, ordinalize) */

Inflector = {
	Inflections: {
	    plural: [
		    [/(quiz)$/i,               "$1zes"  ],
		    [/^(ox)$/i,                "$1en"   ],
		    [/([m|l])ouse$/i,          "$1ice"  ],
		    [/(matr|vert|ind)ix|ex$/i, "$1ices" ],
		    [/(x|ch|ss|sh)$/i,         "$1es"   ],
		    [/([^aeiouy]|qu)y$/i,      "$1ies"  ],
		    [/(hive)$/i,               "$1s"    ],
		    [/(?:([^f])fe|([lr])f)$/i, "$1$2ves"],
		    [/sis$/i,                  "ses"    ],
		    [/([ti])um$/i,             "$1a"    ],
		    [/([i])on$/i,              "$1a"    ],
		    [/(buffal|tomat)o$/i,      "$1oes"  ],
		    [/(bu)s$/i,                "$1ses"  ],
		    [/(alias|status)$/i,       "$1es"   ],
		    [/(octop|vir)us$/i,        "$1i"    ],
		    [/(ax|test)is$/i,          "$1es"   ],
		    [/s$/i,                    "s"      ],
		    [/$/,                      "s"      ]
		],
	    singular: [
		    [/(quiz)zes$/i,                                                    "$1"     ],
		    [/(matr)ices$/i,                                                   "$1ix"   ],
		    [/(vert|ind)ices$/i,                                               "$1ex"   ],
		    [/^(ox)en/i,                                                       "$1"     ],
		    [/(alias|status)es$/i,                                             "$1"     ],
		    [/(octop|vir)i$/i,                                                 "$1us"   ],
		    [/(cris|ax|test)es$/i,                                             "$1is"   ],
		    [/(shoe)s$/i,                                                      "$1"     ],
		    [/(o)es$/i,                                                        "$1"     ],
		    [/(bus)es$/i,                                                      "$1"     ],
		    [/([m|l])ice$/i,                                                   "$1ouse" ],
		    [/(x|ch|ss|sh)es$/i,                                               "$1"     ],
		    [/(m)ovies$/i,                                                     "$1ovie" ],
		    [/(s)eries$/i,                                                     "$1eries"],
		    [/([^aeiouy]|qu)ies$/i,                                            "$1y"    ],
		    [/([lr])ves$/i,                                                    "$1f"    ],
		    [/(tive)s$/i,                                                      "$1"     ],
		    [/(hive)s$/i,                                                      "$1"     ],
		    [/([^f])ves$/i,                                                    "$1fe"   ],
		    [/(^analy)ses$/i,                                                  "$1sis"  ],
		    [/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i, "$1$2sis"],
		    [/([ti])a$/i,                                                      "$1um"   ],
		    [/(n)ews$/i,                                                       "$1ews"  ],
		    [/s$/i,                                                            ""       ]
		],
	    irregular: [
		    ['move',   'moves'   ],
		    ['sex',    'sexes'   ],
		    ['child',  'children'],
		    ['man',    'men'     ],
		    ['woman',  'women'   ],
		    ['person', 'people'  ],
		    ['half',   'halves'  ],
		    ['loaf',   'loaves'  ],
		    ['tooth',  'teeth'   ],
		    ['foot',   'feet'    ],
		    ['die',    'dice'    ]
		],
	    uncountable: [
		    "sheep",
		    "fish",
		    "series",
		    "species",
		    "money",
		    "rice",
		    "information",
		    "equipment",
		    "salmon",
		    "bison",
		    "trout",
		    "pike",
		    "deer",
		    "swine",
		    "clothes"
	    ]
	},
	ordinalize: function(number) {
		if (11 <= parseInt(number) % 100 && parseInt(number) % 100 <= 13) {
			return number + "th";
		} else {
			switch (parseInt(number) % 10) {
				case  1: return number + "st";
				case  2: return number + "nd";
				case  3: return number + "rd";
				default: return number + "th";
			}
		}
	},
	pluralize: function(word) {
	    for (var i = 0; i < Inflector.Inflections.uncountable.length; i++) {
			var uncountable = Inflector.Inflections.uncountable[i];
			if (word.toLowerCase() == uncountable) {
			    return uncountable;
			}
	    }
	    for (var i = 0; i < Inflector.Inflections.irregular.length; i++) {
			var singular = Inflector.Inflections.irregular[i][0];
			var plural   = Inflector.Inflections.irregular[i][1];
			if ((word.toLowerCase() == singular) || (word == plural)) {
			    return plural;
			}
	    }
	    for (var i = 0; i < Inflector.Inflections.plural.length; i++) {
			var regex          = Inflector.Inflections.plural[i][0];
			var replace_string = Inflector.Inflections.plural[i][1];
			if (regex.test(word)) {
			    return word.replace(regex, replace_string);
			}
	    }
	},
	singularize: function(word) {
	    for (var i = 0; i < Inflector.Inflections.uncountable.length; i++) {
			var uncountable = Inflector.Inflections.uncountable[i];
			if (word.toLowerCase() == uncountable) {
			    return uncountable;
			}
	    }
	    for (var i = 0; i < Inflector.Inflections.irregular.length; i++) {
			var singular = Inflector.Inflections.irregular[i][0];
			var plural   = Inflector.Inflections.irregular[i][1];
			if ((word.toLowerCase() == singular) || (word == plural)) {
			    return plural;
			}
	    }
	    for (var i = 0; i < Inflector.Inflections.singular.length; i++) {
			var regex          = Inflector.Inflections.singular[i][0];
			var replace_string = Inflector.Inflections.singular[i][1];
			if (regex.test(word)) {
			    return word.replace(regex, replace_string);
			}
	    }
	}
}

function ordinalize(number) {
	return Inflector.ordinalize(number);
}

String.prototype.pluralize = function(count, plural) {
	if (typeof count == 'undefined') {
		return Inflector.pluralize(this);
	} else {
		return count + ' ' + (1 == parseInt(count) ? this : plural || Inflector.pluralize(this));
	}
}

String.prototype.singularize = function(count) {
	if (typeof count == 'undefined') {
	    return Inflector.singularize(this);
	} else {
	    return count + " " + Inflector.singularize(this);
	}
}