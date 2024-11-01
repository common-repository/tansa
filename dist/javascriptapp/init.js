window.tansa = {};
window.tansa.settings = {};

function tansaStrReplaceAll(source, target, replacement) {
    target = escapeRegExpPattern(target);
    var regExp = new RegExp(target, "ig");
    return source.replace(regExp, replacement);
};

function escapeRegExpPattern(str) {
	return str.replace(/[-\/\\^$*+?.()|[\]{}!]/g, '\\$&');
};

window.initTansaObject = function(){
    tansaExtensionInfo.showFloatingTansaMenu = tansaExtensionInfo.showFloatingTansaMenu == "1";
    
	window.isLingofyMode = undefined;
	tansa.canShowLingofyTheme = undefined;
    window.wpTansaSidebarSelector = ".interface-interface-skeleton__sidebar";
    window.hideTansaPluginSideBarCssClassName = 'hideTansaPluginSideBar';
    window.tansaCustomMenuContainerId = 'tansaCustomMenu';
    window.tansaCustomSubMenuElementId = 'tansaCustomSubMenu';


    if(!tansa.settings)
        tansa.settings = {};
    
    tansaExtensionInfo.isTansaServerURLSet = true;
    if(!tansaExtensionInfo.tansaServerURL){
        console.error("Tansa server URL is not configured in Tansa settings page.")
        tansaExtensionInfo.isTansaServerURLSet = false;
    }

	tansa.settings.baseUrl = createTansaClientBaseURL(tansaExtensionInfo.tansaServerURL);
    tansa.settings.userId =  tansaExtensionInfo.wpUserId;
    tansa.settings.clientExtenstionJs = 'tansa4ClientExtensionSimple.js';
    tansa.settings.theme = 'tansa-default';
    tansa.settings.parentAppId = '55a8be37-d788-4e2e-8116-66c557dbc7b8';
    tansa.settings.licenseKey = tansaExtensionInfo.licenseKey;
    tansa.settings.parentAppVersion = tansaExtensionInfo.wpVersion;
    tansa.settings.extensionName = 'tansa-wordpress';
	tansa.settings.extensionVersion = tansaExtensionInfo.version;
	tansa.settings.langCode = tansaExtensionInfo.parentAppLangCode;
	tansa.settings.connectionMenuRequired = false;
}

function createTansaClientBaseURL(tansaServerURL) {
	tansaServerURL = tansaServerURL.toLowerCase().trim();
	while (tansaServerURL.endsWith("/"))
        tansaServerURL = tansaServerURL.substring(0, tansaServerURL.length - 1);

    return tansaServerURL ? tansaServerURL + "/tansaclient/" : "";
}

function hideTansaPluginSidebar(){
    window.jQuery(window.wpTansaSidebarSelector).addClass(window.hideTansaPluginSideBarCssClassName);
}

function showTansaPluginSidebar(){
    window.jQuery(window.wpTansaSidebarSelector).removeClass(window.hideTansaPluginSideBarCssClassName);
}

function setTansaMenuContentPositionAndStyle(menuContent){
    window.jQuery('#' + window.tansaCustomMenuContainerId).remove();
    window.jQuery('<div id="' + window.tansaCustomMenuContainerId + '" class="tansa">')
                    .appendTo('body')
                    .html(menuContent)
                    .offset(window.getTansaMenuButtonPosition());
    window.jQuery('#' + window.tansaCustomSubMenuElementId).menu().hide();
    window.toggleTansaCustomMenu();
}

function resetMenuContentPositionAndStyle(){
    var position = window.getTansaMenuButtonPosition();
    window.jQuery('#' + window.tansaCustomMenuContainerId).offset(position);
    if(window.tansaMain){
        window.jQuery('#' + window.tansaMain.tansaMenuContainerId).offset(position);
    }
}

function getTansaMenuButtonPosition() {        	
    var tansaButton = window.jQuery('#content_tansaButton');
    var position = tansaButton.offset();
    position.top += tansaButton.outerHeight(true) + 9;
    var tansaSubMenu = window.jQuery('#tansaSubMenu');
    var left = 160;
    if(tansaSubMenu.length > 0){
        left = tansaSubMenu.width() - 70;
    }
    if(window.wpTansaSidebarComponent.tansaCustomMenuLoaded || window.wpTansaSidebarComponent.loadingMenuLoaded){
        left = 72;
    }
    position.left -= left;
    return position;
}

function toggleTansaCustomMenu(){
    window.resetMenuContentPositionAndStyle();
    window.jQuery('#' + window.tansaCustomSubMenuElementId).toggle();
}

function getUILanguageStringValue(uiLangaugeStringsObj){
	var attributes = uiLangaugeStringsObj['@attributes'];
    var langString = attributes.text ||attributes.tansa || ""
    return tansaStrReplaceAll(langString, "%SHORTSYSTEMNAME%", tansaExtensionInfo.shortSystemName);
}