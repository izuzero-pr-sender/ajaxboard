/**
 * AJAXBoard XE Module Javascript
 * Copyright (C) 아약스보드. All rights reserved.
 **/

var buildQuery = function(args, numeric_prefix, separator)
{
	var value, key, tmp = [], that = this;
	
	var buildQueryHelper = function(key, val, separator)
	{
		var k, tmp = [];
		if (val === true)
		{
			val = "1";
		}
		else if (val === false)
		{
			val = "0";
		}
		if (val != null)
		{
			if (jQuery.isPlainObject(val))
			{
				for (k in val)
				{
					if (val[k] != null)
					{
						tmp.push(buildQueryHelper(key + "[" + k + "]", val[k], separator));
					}
				}
				return tmp.join(separator);
			}
			else if (!jQuery.isFunction(val))
			{
				return that.urlencode(key) + "=" + that.urlencode(val);
			}
		}
		else
		{
			return "";
		}
	};
	
	if (!separator)
	{
		separator = "&";
	}
	for (key in args)
	{
		value = args[key];
		if (numeric_prefix && !isNaN(key))
		{
			key = String(numeric_prefix) + key;
		}
		var query = buildQueryHelper(key, value, separator);
		if (query !== "")
		{
			tmp.push(query);
		}
	}
	return tmp.join(separator);
};

var urlencode = function(str)
{
	str = (str + "").toString();
	
	return encodeURIComponent(str)
	.replace(/!/g, "%21")
	.replace(/'/g, "%27")
	.replace(/\(/g, "%28")
	.replace(/\)/g, "%29")
	.replace(/\*/g, "%2A")
	.replace(/%20/g, "+");
};