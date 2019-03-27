// PAGE object for Ajax call:
ajax_page = PAGE
ajax_page {
    typeNum = 110001
    config {
        disableAllHeaderCode = 1
        xhtml_cleaning = 1
        admPanel = 0
        additionalHeaders = Content-type: text/plain
        no_cache = 1
    }
    10 < styles.content.get
    10 {
        stdWrap.trim = 1
        select {
            where = list_type = "vibisajaxmailsubscription_plugkeysubscription"
        }
        renderObj < tt_content.list.20.vibisajaxmailsubscription_plugkeysubscription
    }
}  

// popup - open div
page.2900 = TEXT
page.2900.value = <div class="tx-vibis-ajaxmailsubscription" id="vibis_popup" style="z-index:99; display: none; background: none 0% 0% repeat scroll rgb(141,133,161); padding: 20px; bottom: 10%; right: 1%; height: 180px; width:98%; max-width: 360px; position: fixed; border:1px solid rgb(91,118,173);font-size:12px;">

    // add plugin to each page
page.2901 = USER
page.2901 {
       userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
       pluginName = Plugkeysubscription
       extensionName = VibisAjaxmailsubscription
       controller = Subscription
       vendorName = Vibis
       action = start
       view =< plugin.tx_vibisajaxmailsubscription_plugkeysubscription.view
       settings < plugin.tx_vibisajaxmailsubscription_plugkeysubscription.settings
}

// popup - close div
page.2902 = TEXT
page.2902.value = <br><br><input type="button" value="Sluit" id="vibisPopUpCloseBtn"></div>

page.includeJSFooter {
    vibisajaxmailsubscription_js = EXT:vibis_ajaxmailsubscription/Resources/Public/JavaScript/subscribe.js
}

plugin.tx_vibisajaxmailsubscription_plugkeysubscription {
    view {
        templateRootPaths.0 = EXT:vibis_ajaxmailsubscription/Resources/Private/Templates/
        templateRootPaths.1 = {$plugin.tx_vibisajaxmailsubscription_plugkeysubscription.view.templateRootPath}
        partialRootPaths.0 = EXT:vibis_ajaxmailsubscription/Resources/Private/Partials/
        partialRootPaths.1 = {$plugin.tx_vibisajaxmailsubscription_plugkeysubscription.view.partialRootPath}
        layoutRootPaths.0 = EXT:vibis_ajaxmailsubscription/Resources/Private/Layouts/
        layoutRootPaths.1 = {$plugin.tx_vibisajaxmailsubscription_plugkeysubscription.view.layoutRootPath}
    }
    features {
        #skipDefaultArguments = 1
        # if set to 1, the enable fields are ignored in BE context
        ignoreAllEnableFieldsInBe = 0
        # Should be on by default, but can be disabled if all action in the plugin are uncached
        requireCHashArgumentForActionArguments = 1
    }
    mvc {
        #callDefaultActionIfActionCantBeResolved = 1
    }
    settings.authcode_expiration_time = {$plugin.tx_vibisajaxmailsubscription_plugkeysubscription.settings.authcode_expiration_time} 
    settings.pageUid = {$plugin.tx_vibisajaxmailsubscription_plugkeysubscription.settings.pageUid}
    settings.popUpTime = {$plugin.tx_vibisajaxmailsubscription_plugkeysubscription.settings.popUpTime}
}

# these classes are only used in auto-generated templates
plugin.tx_vibisajaxmailsubscription._CSS_DEFAULT_STYLE (
    textarea.f3-form-error {
        background-color:#FF9F9F;
        border: 1px #FF0000 solid;
    }

    input.f3-form-error {
        background-color:#FF9F9F;
        border: 1px #FF0000 solid;
    }

    .tx-vibis-ajaxmailsubscription table {
        border-collapse:separate;
        border-spacing:10px;
    }

    .tx-vibis-ajaxmailsubscription table th {
        font-weight:bold;
    }

    .tx-vibis-ajaxmailsubscription table td {
        vertical-align:top;
    }

    .typo3-messages .message-error {
        color:red;
    }

    .typo3-messages .message-ok {
        color:green;
    }
)
