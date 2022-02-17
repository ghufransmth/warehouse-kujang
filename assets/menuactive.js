var CURRENT_URL = window.location.href.split('#')[0].split('?')[0],
    $SIDEBAR_MENU = $('#sidebar-menu');

// Sidebar
$(document).ready(function() {
    // TODO: This is some kind of easy fix, maybe we can improve this
    // check active menu
    var segments = CURRENT_URL.split( '/' );
	var iniurl = window.location.origin; 
	var potongurl= iniurl+'/'+segments[3]+'/'+segments[4]+'/'+segments[5]+'/'+segments[6];
    $SIDEBAR_MENU.find('ul a[href="' + potongurl + '"]').parents('li').addClass('active');
    $SIDEBAR_MENU.find('a').filter(function () {
        return this.href == potongurl;
    }).addClass('active').parents('li').slideDown(function() {
    });
});