<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function()
    {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'Vibis.VibisAjaxmailsubscription',
            'Plugkeysubscription',
            'Ajax Mail Subscription'
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('vibis_ajaxmailsubscription', 'Configuration/TypoScript', 'Vibis Ajax Mail Subscription');

            // add flexform
        $extKey = 'vibis_ajaxmailsubscription';
        $extPluginKey = 'Plugkeysubscription';
        $extName = strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($extKey));
        $pluginSignature = $extName . '_' . strtolower($extPluginKey);  
        
        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $extKey . '/Configuration/FlexForms/flexform_Pluginsubscribe.xml');
        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key, pages,recursive';

                

    }
);
