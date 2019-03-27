<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function()
    {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Vibis.VibisAjaxmailsubscription',
            'Plugkeysubscription',
            [
                'Subscription' => 'start, status'
            ],
            // non-cacheable actions
            [
                'Subscription' => 'start, status'
            ]
        );

    // wizards
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        'mod {
            wizards.newContentElement.wizardItems.plugins {
                elements {
                    plugkeysubscription {
                        iconIdentifier = vibis_ajaxmailsubscription-plugin-plugkeysubscription
                        title = LLL:EXT:vibis_ajaxmailsubscription/Resources/Private/Language/locallang_db.xlf:tx_vibis_ajaxmailsubscription_plugkeysubscription.name
                        description = LLL:EXT:vibis_ajaxmailsubscription/Resources/Private/Language/locallang_db.xlf:tx_vibis_ajaxmailsubscription_plugkeysubscription.description
                        tt_content_defValues {
                            CType = list
                            list_type = vibisajaxmailsubscription_plugkeysubscription
                        }
                    }
                }
                show = *
            }
       }'
    );
		$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
		
			$iconRegistry->registerIcon(
				'vibis_ajaxmailsubscription-plugin-plugkeysubscription',
				\TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
				['source' => 'EXT:vibis_ajaxmailsubscription/Resources/Public/Icons/user_plugin_plugkeysubscription.svg']
			);
		
    }
);
