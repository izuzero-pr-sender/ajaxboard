/**
 * AJAXBoard XE Module Javascript
 * Copyright (C) 아약스보드. All rights reserved.
 **/

jQuery(function($)
{
	$("a.modalAnchor._member").bind("before-open.mw", function()
	{
		var $memberList = $("#memberList tbody :checked");
		if (!$memberList.length)
		{
			alert(xe.lang.msg_select_push_notifications);
			return false;
		}
		
		var member_info, member_srl;
		
		$("#message").val("");
		$("#popupBody").empty();
		
		for (var i = 0; i < $memberList.length; i++)
		{
			member_info = $memberList.eq(i).val().split("|@|");
			member_srl  = member_info.shift();
			$tr = $("<tr></tr>");
			for (var j in member_info)
			{
				var info = member_info[j];
				var $td  = $("<td></td>").text(info);
				$tr.append($td);
			}
			$tr.append('<td><input type="hidden" name="receiver_srls[]" value="' + member_srl + '"/></td>');
			$("#popupBody").append($tr);
		}
	});
});