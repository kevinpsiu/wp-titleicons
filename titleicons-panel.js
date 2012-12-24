jQuery(document).ready(function($){
	$(".titleicons-iconbox").click(function(){
		var iconfilename = $(this).attr('alt');
		$("#titleicons-iconfilename").val(iconfilename);
		var baseurl = $("#titleicons-currenticon").attr('src').substr(0,$("#titleicons-currenticon").attr('src').lastIndexOf('/')+1);
		$("#titleicons-currenticon").attr('src', baseurl+iconfilename);
	});
});