
plugin.tx_vibisajaxmailsubscription_plugkeysubscription {
    view {
        # cat=plugin.tx_vibisajaxmailsubscription_plugkeysubscription/file; type=string; label=Path to template root (FE)
        templateRootPath = EXT:vibis_ajaxmailsubscription/Resources/Private/Templates/
        # cat=plugin.tx_vibisajaxmailsubscription_plugkeysubscription/file; type=string; label=Path to template partials (FE)
        partialRootPath = EXT:vibis_ajaxmailsubscription/Resources/Private/Partials/
        # cat=plugin.tx_vibisajaxmailsubscription_plugkeysubscription/file; type=string; label=Path to template layouts (FE)
        layoutRootPath = EXT:vibis_ajaxmailsubscription/Resources/Private/Layouts/
    }
    settings{
        # cat=plugin.tx_vibisajaxmailsubscription_plugkeysubscription/General/01; type=integer; label=PageId of subscription handler
        pageUid = 
        # cat=plugin.tx_vibisajaxmailsubscription_plugkeysubscription/General/02; type=integer; label=Authcode expire time
        authcode_expiration_time = 60
        # cat=plugin.tx_vibisajaxmailsubscription_plugkeysubscription/General/03; type=integer; label=PopUp show time interval in Seconds
        popUpTime = 5
        
    }
}
